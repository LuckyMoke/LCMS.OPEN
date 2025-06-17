<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-06-17 10:40:36
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
        ini_set('session.gc_maxlifetime', 86400);
        $stime = time() + ($_L['config']['admin']['sessiontime'] > 0 ? $_L['config']['admin']['sessiontime'] * 60 : 21600);
        if ($_L['form']['rootsid']) {
            // 请确保rootsid在每个客户端唯一
            $userid  = strtolower($_L['form']['rootsid']);
            $userid  = str_replace("lcms-", "", $userid);
            $userid  = preg_replace("/[^a-z0-9]/", "", $userid);
            $uidlong = strlen($userid);
            if ($uidlong < 32) {
                ajaxout(0, "rootsid仅支持字母、数字，长度不能小于32位，并且每个用户唯一。");
            } elseif ($uidlong > 32) {
                $userid = substr(md5($userid . PATH_WEB), 8, 16) . substr($userid, -16);
            }
            $userid = "lcms-{$userid}";
        } else {
            $ltime = time() + 15552000;
            if ($_COOKIE['LCMSCID']) {
                $cookie = ssl_decode_gzip($_COOKIE['LCMSCID'], PATH_WEB);
            }
            if ($cookie) {
                setcookie("LCMSCID", $_COOKIE['LCMSCID'], $ltime, "/", "", 0, true);
            } else {
                $cookie = session_create_id();
                setcookie("LCMSCID", ssl_encode_gzip($cookie, PATH_WEB), $ltime, "/", "", 0, true);
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
            if (!$_L['SESSION']['redisid']) {
                LOAD::plugin("Redis/rds");
                $_L['SESSION'] = array_merge($_L['SESSION'], [
                    "redis"   => new RDS(),
                    "redisid" => $SESSION['id'],
                ]);
                $SESSION['redis']   = $_L['SESSION']['redis'];
                $SESSION['redisid'] = $_L['SESSION']['redisid'];
            }
            $etime = $SESSION['redis']->do->hGet($SESSION['redisid'], "LCMSSIDTIME");
            if ($etime && $etime < time()) {
                $SESSION['redis']->do->hDel($SESSION['redisid'], "LCMSADMIN");
            }
            $SESSION['redis']->do->hSet($SESSION['redisid'], "LCMSSIDTIME", $SESSION['time']);
            $SESSION['redis']->do->expire($SESSION['redisid'], 604800);
        } else {
            session_name("LCMSSID");
            session_id($SESSION['id']);
            session_start();
            $etime = $_SESSION['LCMSSIDTIME'];
            if ($etime && $etime < time()) {
                unset($_SESSION["LCMSADMIN"]);
            }
            $_SESSION['LCMSSIDTIME'] = $SESSION['time'];
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
            $SESSION['redis']->do->hSet($SESSION['redisid'], $name, $value);
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
            $value = $SESSION['redis']->do->hGet($SESSION['redisid'], $name);
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
            $arr = $SESSION['redis']->do->hGetAll($SESSION['redisid']);
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
            $SESSION['redis']->do->hDel($SESSION['redisid'], $name);
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
            $SESSION['redis']->do->delete($SESSION['redisid']);
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
