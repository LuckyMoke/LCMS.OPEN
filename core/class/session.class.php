<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-03 16:41:29
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
     * @启动SESSION
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
            $expire_time = self::$redis->$do->hGet(self::$userid, "LCMSSIDTIME");
            if ($expire_time && $expire_time < time()) {
                self::$redis->$do->hDel(self::$userid, "LCMSADMIN");
            }
            self::$redis->$do->hSet(self::$userid, "LCMSSIDTIME", time() + intval($session_time));
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
     * @设置SESSION
     * @param {*}
     * @return {*}
     */
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
    /**
     * @获取SESSION
     * @param {*}
     * @return {*}
     */
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
    /**
     * @获取所有SESSION
     * @param {*}
     * @return {*}
     */
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
    /**
     * @删除SESSION
     * @param {*}
     * @return {*}
     */
    public static function del($name)
    {
        if (self::$type == "1") {
            self::$redis->$do->hDel(self::$userid, $name);
        } else {
            unset($_SESSION[$name]);
        }
    }
    /**
     * @删除所有SESSION
     * @param {*}
     * @return {*}
     */
    public static function delall()
    {
        if (self::$type == "1") {
            self::$redis->$do->delete(self::$userid);
        } else {
            session_destroy();
        }
    }
    /**
     * @获取SESSIONID
     * @param {*}
     * @return {*}
     */
    public static function getid()
    {
        return self::$userid;
    }
}
