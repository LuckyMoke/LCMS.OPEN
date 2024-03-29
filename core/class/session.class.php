<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-03-11 00:07:56
 * @Description:SESSION操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SESSION
{
    /**
     * @description: 初始化SESSION
     * @param {*}
     * @return {*}
     */
    public static function init()
    {
        global $_L;
        ini_set("session.sid_length", 32);
        ini_set("session.sid_bits_per_character", 5);
        ini_set("session.use_cookies", 0);
        $stime = $_L['config']['admin']['sessiontime'];
        $stime = $stime > "0" ? $stime * 60 : 86400;
        if ($_L['form']['rootsid']) {
            // 请确保rootsid在每个客户端唯一
            $userid = $_L['form']['rootsid'];
            $userid = preg_replace("/[^A-Za-z0-9-]/", "", $userid);
            if (!in_string($userid, "lcms-") || strlen($userid) > 37) {
                $userid = "lcms-" . substr(md5($userid . PATH_WEB), 8, 16) . substr($userid, -16);
            }
        } else {
            if ($_COOKIE['LCMSCID']) {
                $cookie = ssl_decode(urlsafe_base64_decode($_COOKIE['LCMSCID']), PATH_WEB);
            }
            if (!$cookie) {
                $cookie = session_create_id();
                setcookie("LCMSCID", urlsafe_base64_encode(ssl_encode($cookie, PATH_WEB)), 0, "/", "", 0, true);
            }
            $userid = "lcms-{$cookie}";
        }
        $_L['SESSION'] = [
            "id"   => strtolower($userid),
            "type" => $_L['config']['admin']['session_type'],
            "time" => intval($stime),
        ];
    }
    /**
     * @description: 启动SESSION
     * @param {*}
     * @return {*}
     */
    public static function start()
    {
        global $_L;
        $SESSION = $_L['SESSION'];
        if ($SESSION['type'] == "1") {
            if (!$SESSION['redis']) {
                load::plugin("Redis/rds");
                $SESSION['redis'] = $_L['SESSION']['redis'] = new RDS();
            }
            $etime = $SESSION['redis']->do->hGet($SESSION['id'], "LCMSSIDTIME");
            if ($etime && $etime < time()) {
                $SESSION['redis']->do->hDel($SESSION['id'], "LCMSADMIN");
            }
            $SESSION['redis']->do->hSet($SESSION['id'], "LCMSSIDTIME", time() + $SESSION['time']);
        } else {
            session_name("LCMSSID");
            session_id($SESSION['id']);
            session_start();
            $etime = $_SESSION['LCMSSIDTIME'];
            if ($etime && $etime < time()) {
                unset($_SESSION["LCMSADMIN"]);
            }
            $_SESSION['LCMSSIDTIME'] = time() + $SESSION['time'];
        }
        return $SESSION;
    }
    /**
     * @description: 设置SESSION
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function set($name, $value)
    {
        $SESSION = self::start();
        if ($SESSION['type'] == "1") {
            if (is_object($value) || is_array($value)) {
                $value = arr2sql($value);
            }
            $SESSION['redis']->do->hSet($SESSION['id'], $name, $value);
        } else {
            $_SESSION[$name] = $value;
            session_write_close();
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
        $SESSION = self::start();
        if ($SESSION['type'] == "1") {
            $value = $SESSION['redis']->do->hGet($SESSION['id'], $name);
            if (is_serialize($value)) {
                return sql2arr($value);
            }
        } else {
            $value = $_SESSION[$name];
            session_write_close();
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
        $SESSION = self::start();
        if ($SESSION['type'] == "1") {
            $arr = $SESSION['redis']->do->hGetAll($SESSION['id']);
            foreach ($arr as $key => $val) {
                if (is_serialize($val)) {
                    $val = sql2arr($val);
                }
                $arr[$key] = $val;
            }
        } else {
            $arr = $_SESSION;
            session_write_close();
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
        $SESSION = self::start();
        if ($SESSION['type'] == "1") {
            $SESSION['redis']->do->hDel($SESSION['id'], $name);
        } else {
            unset($_SESSION[$name]);
            session_write_close();
        }
    }
    /**
     * @description: 删除所有SESSION
     * @param {*}
     * @return {*}
     */
    public static function delall()
    {
        $SESSION = self::start();
        if ($SESSION['type'] == "1") {
            $SESSION['redis']->do->delete($SESSION['id']);
        } else {
            session_destroy();
        }
    }
    /**
     * @description: 获取SESSIONID
     * @param bool $type
     * @return string
     */
    public static function getid($type = false)
    {
        $SESSION = self::start();
        return $SESSION['id'];
    }
}
