<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:16
 * @LastEditTime: 2024-03-10 23:47:31
 * @Description: 全局公共类
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_func('file');
load::sys_class('sqlpdo');
load::sys_class('mysql');
load::sys_func('mysql');
load::sys_class('session');
load::sys_class('http');
class common
{
    /**
     * @description: 初始化
     * @return {*}
     */
    public function __construct()
    {
        global $_L;
        header("X-Powered-By: PanQi-PHP-Framework; PanQi-CMS");
        $this->load_common_mysql();
        $this->load_common_form();
        $this->load_common_tables();
        $this->load_common_config();
        load::sys_class('developer');
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
        parse_str(substr(strstr(HTTP_URI, '?'), 1), $QUERY);
        $forms = array_merge($QUERY ?: [], $_COOKIE ?: [], $_POST ?: [], $_GET ?: []);
        foreach ($forms as $key => $val) {
            if ($val !== "") {
                if (in_array($key, [
                    "t", "n", "c", "a", "action", "cls", "do",
                ])) {
                    if (preg_match("/^[a-zA-Z0-9_-]+$/", $val)) {
                        $_L['form'][$key] = $val;
                    } else {
                        LCMS::X(403, "参数不合法");
                    }
                } else {
                    $_L['form'][$key] = filterform($val);
                }
            }
        }
    }
    /**
     * @description: 加载数据表
     * @return {*}
     */
    protected function load_common_tables()
    {
        global $_L;
        $table = $_L['DB']->get_tables();
        foreach ($table as $val) {
            $name          = str_replace($_L['mysql']['pre'], "", $val);
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
        // 上传文件格式过滤
        $mimelist = strtolower($_L['config']['admin']['mimelist']);
        $mimelist = explode("|", $mimelist);
        foreach ([
            "php", "php3", "php4", "php5", "pht", "exe", "cgi",
        ] as $mime) {
            $index = array_search($mime, $mimelist);
            if ($index !== false) {
                unset($mimelist[$index]);
            }
        }
        $_L['config']['admin']['mimelist'] = implode("|", $mimelist);
    }
}
