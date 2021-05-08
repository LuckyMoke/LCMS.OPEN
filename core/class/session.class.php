<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-05-08 02:46:24
 * @Description:SESSION操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SESSION
{
    private static $type = "0";
    private static $userid;
    private static $redis;
    /**
     * @description: 启动SESSION
     * @param {*}
     * @return {*}
     */
    public static function start()
    {
        global $_L;
        self::$type   = $_L['config']['admin']['session_type'];
        $session_time = $_L['config']['admin']['sessiontime'];
        $session_time = $session_time > "0" ? $session_time * 60 : 86400;
        if ($_L['form']['rootsid']) {
            // 请确保rootsid在每个客户端唯一
            self::$userid = "LCMS" . strtoupper($_L['form']['rootsid']);
            self::$userid = preg_replace("/[^A-Z0-9]/i", "", self::$userid);
        } else {
            if (empty($_COOKIE['LCMSCID'])) {
                $cookie = strtoupper(substr(md5(time() . randstr(6) . CLIENT_IP . $_SERVER['HTTP_USER_AGENT']), 8, 16)) . randstr(6);
                setcookie("LCMSCID", $cookie, 0, "/", "", 0, true);
            } else {
                $cookie = $_COOKIE['LCMSCID'];
            }
            self::$userid = "LCMS{$cookie}";
        }
        if (self::$type == "1") {
            load::plugin("Redis/rds");
            self::$redis = new RDS();
            $expire_time = self::$redis->do->hGet(self::$userid, "LCMSSIDTIME");
            if ($expire_time && $expire_time < time()) {
                self::$redis->do->hDel(self::$userid, "LCMSADMIN");
            }
            self::$redis->do->hSet(self::$userid, "LCMSSIDTIME", time() + intval($session_time));
        } else {
            session_name("LCMSSID");
            session_id(self::$userid);
            session_start();
            $expire_time = $_SESSION['LCMSSIDTIME'];
            if ($expire_time && $expire_time < time()) {
                unset($_SESSION["LCMSADMIN"]);
            }
            $_SESSION['LCMSSIDTIME'] = time() + intval($session_time);
        }
    }
    /**
     * @description: 设置SESSION
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function set($name, $value)
    {
        if (self::$type == "1") {
            if (is_object($value) || is_array($value)) {
                $value = arr2sql($value);
            }
            self::$redis->do->hSet(self::$userid, $name, $value);
        } else {
            $_SESSION[$name] = $value;
        }
        return $value;
    }
    /**
     * @description: 获取SESSION
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (self::$type == "1") {
            $value = self::$redis->do->hGet(self::$userid, $name);
            if (is_serialize($value)) {
                return sql2arr($value);
            }
        } else {
            $value = $_SESSION[$name];
        }
        return $value;
    }
    /**
     * @description: 获取所有SESSION
     * @param {*}
     * @return array
     */
    public static function getall()
    {
        if (self::$type == "1") {
            $arr = self::$redis->do->hGetAll(self::$userid);
            foreach ($arr as $key => $val) {
                if (is_serialize($val)) {
                    $val = sql2arr($val);
                }
                $arr[$key] = $val;
            }
        } else {
            $arr = $_SESSION;
        }
        return $arr;
    }
    /**
     * @description: 删除SESSION
     * @param string $name
     * @return {*}
     */
    public static function del($name)
    {
        if (self::$type == "1") {
            self::$redis->do->hDel(self::$userid, $name);
        } else {
            unset($_SESSION[$name]);
        }
    }
    /**
     * @description: 删除所有SESSION
     * @param {*}
     * @return {*}
     */
    public static function delall()
    {
        if (self::$type == "1") {
            self::$redis->do->delete(self::$userid);
        } else {
            session_destroy();
        }
    }
    /**
     * @description: 获取SESSIONID
     * @param {*}
     * @return string
     */
    public static function getid()
    {
        return self::$userid;
    }
}
