<?php
defined('IN_LCMS') or exit('No permission');
class SESSION
{
    private static $type = "0";
    private static $userid;
    private static $redis;
    public static function start()
    {
        global $_L;
        self::$type   = $_L['config']['admin']['session_type'];
        $session_time = $_L['config']['admin']['sessiontime'];
        $session_time = $session_time > "0" ? $session_time * 60 : 86400;
        if (!$_COOKIE['LCMSCOOKIEID'] || $_COOKIE['LCMSCOOKIEID'] == "" || $_COOKIE['LCMSCOOKIEID'] == null || $_COOKIE['LCMSCOOKIEID'] == " ") {
            $cookie = md5(time() . randstr(32) . CLIENT_IP);
            setcookie("LCMSCOOKIEID", $cookie, 0, "/", "", 0, true);
        } else {
            $cookie = $_COOKIE['LCMSCOOKIEID'];
        }
        self::$userid = "lcms-session-" . md5($_SERVER['HTTP_USER_AGENT'] . $cookie);
        if (self::$type == "1") {
            load::plugin("Redis/rds");
            self::$redis = new RDS();
            $expire_time = self::$redis->$do->hGet(self::$userid, "LCMSSESSIONTIME");
            if ($expire_time && $expire_time < time()) {
                self::$redis->$do->delete(self::$userid);
            }
            self::$redis->$do->hSet(self::$userid, "LCMSSESSIONTIME", time() + intval($session_time));
        } else {
            session_id(self::$userid);
            session_start();
            $expire_time = $_SESSION['LCMSSESSIONTIME'];
            if ($expire_time && $expire_time < time()) {
                $_SESSION = [];
            }
            $_SESSION['LCMSSESSIONTIME'] = time() + intval($session_time);
        }
    }
    public static function set($name, $value)
    {
        self::start();
        if (self::$type == "1") {
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            self::$redis->$do->hSet(self::$userid, $name, $value);
        } else {
            $_SESSION[$name] = $value;
        }
        return $value;
    }
    public static function get($name)
    {
        self::start();
        if (self::$type == "1") {
            $value      = self::$redis->$do->hGet(self::$userid, $name);
            $value_serl = @unserialize($value);
            if (is_object($value_serl) || is_array($value_serl)) {
                return $value_serl;
            }
        } else {
            $value = $_SESSION[$name];
        }
        return $value;
    }
    public static function getall()
    {
        self::start();
        if (self::$type == "1") {
            $arr = self::$redis->$do->hGetAll(self::$userid);
            foreach ($arr as $key => $val) {
                $val_serl = @unserialize($val);
                if (is_object($val_serl) || is_array($val_serl)) {
                    $arr[$key] = $val_serl;
                    continue;
                }
                $arr[$key] = $val;
            }
        } else {
            $arr = $_SESSION;
        }
        return $arr;
    }
    public static function del($name)
    {
        self::start();
        if (self::$type == "1") {
            self::$redis->$do->hDel(self::$userid, $name);
        } else {
            unset($_SESSION[$name]);
        }
    }
    public static function delall()
    {
        self::start();
        if (self::$type == "1") {
            self::$redis->$do->delete(self::$userid);
        } else {
            session_destroy();
        }
    }
    public static function getid()
    {
        self::start();
        return self::$userid;
    }
}
