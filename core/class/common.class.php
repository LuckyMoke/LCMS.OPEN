<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:16
 * @LastEditTime: 2024-12-18 19:48:37
 * @Description: 全局公共类
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_func('file');
LOAD::sys_class('sqlpdo');
LOAD::sys_class('mysql');
LOAD::sys_func('mysql');
LOAD::sys_class('session');
LOAD::sys_class('http');
class common
{
    /**
     * @description: 初始化
     * @return {*}
     */
    public function __construct()
    {
        global $_L;
        header("X-Framework: e79b98e4bc81c2ae504850e5bc80e58f91e6a186e69eb6");
        $this->load_common_mysql();
        $this->load_common_form();
        $this->load_common_tables();
        $this->load_common_config();
        $this->load_common_rules();
        LOAD::sys_class('developer');
        SESSION::init();
    }
    /**
     * @description: 加载数据库
     * @return {*}
     */
    protected function load_common_mysql()
    {
        global $_L;
        if (is_file(PATH_CORE . 'config.php')) {
            require_once PATH_CORE . 'config.php';
            if ($_L['mysql']['slave']['on'] != 1) {
                unset($_L['mysql']['slave']);
            }
            $_L['DB'] = new MYSQL($_L['mysql']);
        } else {
            okinfo("/install/");
            exit;
        }
    }
    /**
     * @description: 表单过滤
     * @return {*}
     */
    protected function load_common_form()
    {
        global $_L;
        isset($_REQUEST['GLOBALS']) && exit('Access Error');
        $uris = explode("?", $_SERVER['REQUEST_URI']);
        $uris = array_values(array_filter($uris ?: ["/"]));
        $uriq = [];
        if ($uris[1]) {
            parse_str($uris[1], $querys);
            $querys = $querys ?: [];
            $querys = filterform($querys);
            $getUrl = function ($querys, $name = "") use (&$getUrl) {
                $uriq = [];
                if (is_array($querys)) {
                    foreach ($querys as $n => $q) {
                        $uriq = array_merge($uriq, $getUrl($q, $name ? "{$name}[{$n}]" : $n));
                    }
                } else {
                    $uriq[] = "{$name}=" . urlencode($querys);
                }
                return $uriq ?: [];
            };
            $_L['form'] = array_merge($_L['form'] ?: [], $querys);
            $uriq       = $getUrl($querys);
        }
        $uris = $uriq ? "{$uris[0]}?" . implode("&", $uriq) : $uris[0];
        define("HTTP_URI", $uris);
        $forms = array_merge($_POST ?: [], $_GET ?: []);
        $forms = filterform($forms);
        $_GET  = [];
        foreach ($forms as $key => $val) {
            if (
                in_array($key, ["rootid", "t", "n", "c", "a", "action", "cls", "do"]) &&
                ($val !== "" && !preg_match("/^[a-zA-Z0-9_-]+$/", $val))
            ) {
                LCMS::X(403, "参数不合法");
            }
            $_L['form'][$key] = $val;
        }
        $cookies      = $_COOKIE ?: [];
        $cookies      = filterform($cookies);
        $_L['cookie'] = $_COOKIE = $cookies;
    }
    /**
     * @description: 加载数据表
     * @return {*}
     */
    protected function load_common_tables()
    {
        global $_L;
        $table = $_L['DB']->get_tables();
        $pre   = $_L['mysql']['pre'];
        foreach ($table as $val) {
            if (!$val || !preg_match("/^$pre/", $val)) {
                continue;
            }
            $name          = preg_replace("/^$pre/", "", $val);
            $tables[$name] = $val;
        }
        $_L['table'] = $tables;
    }
    /**
     * @description: 获取网站基本配置
     * @return {*}
     */
    protected function load_common_config()
    {
        global $_L;
        $_L['config']['admin'] = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        $_L['config']['web'] = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "web",
            "lcms" => true,
        ]);
        if ($_L['config']['admin']['development']) {
            $_L['config']['ver'] = date("Y.mdHis");
            ob_start();
        } else {
            $version = PATH_CORE . "version";
            if (is_file($version)) {
                $_L['config']['ver'] = file_get_contents($version);
            }
        }
        $mimelist = strtolower($_L['config']['admin']['mimelist']);
        $mimelist = explode("|", $mimelist);
        $mimelist = array_diff($mimelist, [
            "exe", "bat", "cmd", "sh", "com", "scr", "pif", "cpl", "msi", "msp", "reg", "php", "php3", "php4", "php5", "php6", "php7", "phps", "phar", "pht", "phtm", "phtml", "asp", "aspx", "jsp", "cfm", "cgi", "pl", "py", "rb", "htacess", "ini",
        ]);
        $_L['config']['admin']['mimelist'] = implode("|", $mimelist);
    }
    /**
     * @description: 获取网站开发配置
     * @return {*}
     */
    protected function load_common_rules()
    {
        global $_L;
        $_L['developer']['rules'] = array_merge([
            "password" => [
                "pattern" => "/^(?=.*[a-zA-Z])(?=.*\d).{10,}$/",
                "tips"    => "密码必需包含字母+数字，长度不少于10位",
            ],
        ], $_L['developer']['rules'] ?: []);
    }
}
