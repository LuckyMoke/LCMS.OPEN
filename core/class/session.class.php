<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2026-02-09 12:08:58
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
        $expire = $_L['config']['admin']['sessiontime'];
        $expire = $expire > 0 ? $expire * 60 : 2592000;
        $expire = intval($expire);
        $stime  = intval(time() + $expire);
        ini_set("session.sid_length", 32);
        ini_set("session.sid_bits_per_character", 5);
        ini_set("session.use_cookies", 0);
        ini_set("session.gc_maxlifetime", 7200);
        ini_set("session.gc_divisor", 100);
        if ($_L['form']['rootsid']) {
            // 请确保rootsid在每个客户端唯一
            $cookie  = strtolower($_L['form']['rootsid']);
            $cookie  = str_replace("lcms-", "", $cookie);
            $cookie  = preg_replace("/[^a-z0-9]/", "", $cookie);
            $uidlong = strlen($cookie);
            if ($uidlong < 32) {
                ajaxout(0, "rootsid仅支持字母、数字，长度不能小于32位，并且每个用户唯一。");
            } elseif ($uidlong > 32) {
                $cookie = substr(md5($cookie . PATH_WEB), 8, 16) . substr($cookie, -16);
            }
        } else {
            $ltime = time() + 15552000;
            if ($_COOKIE['LCMSCID']) {
                $cookie = ssl_decode_gzip($_COOKIE['LCMSCID'], PATH_WEB);
            }
            if ($cookie) {
                setcookie("LCMSCID", $_COOKIE['LCMSCID'], [
                    "expires"  => $ltime,
                    "path"     => "/",
                    "secure"   => false,
                    "httponly" => true,
                ]);
            } else {
                $cookie = session_create_id();
                setcookie("LCMSCID", ssl_encode_gzip($cookie, PATH_WEB), [
                    "expires"  => $ltime,
                    "path"     => "/",
                    "secure"   => false,
                    "httponly" => true,
                ]);
            }
        }
        $_L['SESSION'] = [
            "id" => strtolower("lcms-{$cookie}"),
            "redisid" => strtolower("lcms:sess:{$cookie}"),
            "type"   => intval($_L['config']['admin']['session_type']),
            "expire" => $expire,
            "stime"  => $stime,
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
        $init = $_L['SESSION'];
        if ($init['type'] == 1) {
            if (!$init['redis']) {
                LOAD::plugin("Redis/rds");
                $init = $_L['SESSION'] = array_merge($init, [
                    "redis" => new RDS(),
                ]);
            }
            $init['redis']->do->hSet($init['redisid'], "lcms:sys:userexpire", $init['stime']);
            $init['redis']->do->expire($init['redisid'], 7200);
        } else {
            session_name("LCMSSID");
            session_id($init['id']);
            session_start();
            $etime = $_SESSION['lcms:sys:userexpire'];
            if ($etime && $etime < time()) {
                unset($_SESSION["LCMSADMIN"]);
            }
            $_SESSION['lcms:sys:userexpire'] = $init['stime'];
        }
        return $init;
    }
    /**
     * @description: 设置SESSION
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function set($name, $value)
    {
        $init = self::start();
        if ($init['type'] == 1) {
            if (is_object($value) || is_array($value)) {
                $value = arr2sql($value);
            }
            $init['redis']->do->hSet($init['redisid'], $name, $value);
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
        $init = self::start();
        if ($init['type'] == 1) {
            $value = $init['redis']->do->hGet($init['redisid'], $name);
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
        $init = self::start();
        if ($init['type'] == 1) {
            $arr = $init['redis']->do->hGetAll($init['redisid']);
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
        $init = self::start();
        if ($init['type'] == 1) {
            $init['redis']->do->hDel($init['redisid'], $name);
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
        $init = self::start();
        if ($init['type'] == 1) {
            $init['redis']->do->delete($init['redisid']);
        } else {
            session_destroy();
        }
    }
    /**
     * @description: 获取SESSIONID
     * @param bool $type
     * @return string
     */
    public static function getid()
    {
        $init = self::start();
        return $init['id'];
    }
}
