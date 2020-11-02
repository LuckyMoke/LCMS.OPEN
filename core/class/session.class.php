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
        if ($_L['form']['lcmscookie']) {
            // 如果请求参数中含有lcmscookie字段内容，那么sessionID为指定的
            self::$userid = "LCMSSESSIONID" . strtoupper(md5($_L['form']['lcmscookie']));
        } else {
            if (!$_COOKIE['LCMSSESSIONID'] || $_COOKIE['LCMSSESSIONID'] == "" || $_COOKIE['LCMSSESSIONID'] == null || $_COOKIE['LCMSSESSIONID'] == " ") {
                $cookie = strtoupper(md5(time() . randstr(32) . CLIENT_IP));
                setcookie("LCMSSESSIONID", $cookie, 0, "/", "", 0, true);
            } else {
                $cookie = $_COOKIE['LCMSSESSIONID'];
            }
            self::$userid = "LCMSSESSIONID" . strtoupper(md5($_SERVER['HTTP_USER_AGENT'] . $cookie));
        }
        if (self::$type == "1") {
            load::plugin("Redis/rds");
            self::$redis = new RDS();
            $expire_time = self::$redis->$do->hGet(self::$userid, "LCMSSESSIONIDTIME");
            if ($expire_time && $expire_time < time()) {
                self::$redis->$do->hDel(self::$userid, "LCMSADMIN");
            }
            self::$redis->$do->hSet(self::$userid, "LCMSSESSIONIDTIME", time() + intval($session_time));
        } else {
            session_id(self::$userid);
            session_start();
            $expire_time = $_SESSION['LCMSSESSIONIDTIME'];
            if ($expire_time && $expire_time < time()) {
                unset($_SESSION["LCMSADMIN"]);
            }
            $_SESSION['LCMSSESSIONIDTIME'] = time() + intval($session_time);
        }
    }
    public static function set($name, $value)
    {
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
        if (self::$type == "1") {
            self::$redis->$do->hDel(self::$userid, $name);
        } else {
            unset($_SESSION[$name]);
        }
    }
    public static function delall()
    {
        if (self::$type == "1") {
            self::$redis->$do->delete(self::$userid);
        } else {
            session_destroy();
        }
    }
    public static function getid()
    {
        return self::$userid;
    }
}
