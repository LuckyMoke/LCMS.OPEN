<?php
defined('IN_LCMS') or exit('No permission');
class SESSION
{
    public static function start()
    {
        global $_L;
        if (!$_COOKIE['LCMSCOOKIEID'] || $_COOKIE['LCMSCOOKIEID'] == "" || $_COOKIE['LCMSCOOKIEID'] == null || $_COOKIE['LCMSCOOKIEID'] == " ") {
            $cookie = md5(time() . randstr(32) . CLIENT_IP);
            setcookie("LCMSCOOKIEID", $cookie, 0, "/", "", 0, true);
        } else {
            $cookie = $_COOKIE['LCMSCOOKIEID'];
        }
        $userid = md5($_SERVER['HTTP_USER_AGENT'] . $cookie);
        session_id($userid);
        session_start();
        $session_time = $_SESSION['LCMSSESSIONTIME'];
        if ($session_time && $session_time < time()) {
            $_SESSION = array();
        } else {
            $_SESSION['LCMSSESSIONTIME'] = time() + ($_L['config']['admin']['sessiontime'] > "0" ? (int) $_L['config']['admin']['sessiontime'] * 60 : 86400);
        }
    }
    public static function set($name, $value)
    {
        self::start();
        $_SESSION[$name] = $value;
        return $value;
    }
    public static function get($name)
    {
        self::start();
        return $_SESSION[$name];
    }
    public static function del($name)
    {
        self::start();
        unset($_SESSION[$name]);
    }
    public static function delAll()
    {
        self::start();
        session_destroy();
    }
    public static function getid()
    {
        self::start();
        return session_id();
    }
}
