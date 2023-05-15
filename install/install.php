<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-01-05 14:12:27
 * @LastEditTime: 2023-05-04 15:52:04
 * @Description: 安装处理文件
 * Copyright 2023 运城市盘石网络科技有限公司
 */
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
date_default_timezone_set("Asia/Shanghai");
define("IN_LCMS", true);
define("PATH_WEB", substr(dirname(__FILE__), 0, -7));
define("PATH_APP", PATH_WEB . "app/");
define("PATH_CACHE", PATH_WEB . "cache/");
define("PATH_PUBLIC", PATH_WEB . "public/");
define("PATH_UPLOAD", PATH_WEB . "upload/");
define("PATH_CORE", PATH_WEB . "core/");
define("PATH_CORE_CLASS", PATH_WEB . "core/class/");
define("PATH_CORE_FUNC", PATH_WEB . "core/function/");
define("PATH_CORE_PLUGIN", PATH_WEB . "core/plugin/");
define("PHP_FILE", basename(__FILE__));
define("PHP_SELF", htmlentities($_SERVER['PHP_SELF']) == "" ? $_SERVER['SCRIPT_NAME'] : htmlentities($_SERVER['PHP_SELF']));
define("SYS_TIME", time());
define("HTTP_PORT", $_SERVER['SERVER_PORT']);
define("HTTP_TOP", $_SERVER['HTTP_REFERER']);
define("HTTP_QUERY", $_SERVER['REQUEST_URI']);
define("SERVER_IP", $_SERVER['SERVER_ADDR']);
define("PAGE_START", microtime(true));
require_once PATH_CORE_FUNC . "common.func.php";
require_once PATH_CORE_FUNC . 'file.func.php';
require_once PATH_CORE_CLASS . "lcms.class.php";
require_once PATH_CORE_CLASS . "sqlpdo.class.php";
is_file(PATH_CORE . "install.lock") && ajaxout(404, [
    "title" => "安装失败",
    "msg"   => "此框架已经安装过了，如需重新安装，请手动删除 /core/install.lock 文件",
]);
PHP_SELF != "/install/install.php" && ajaxout(404, [
    "title" => "安装失败",
    "msg"   => "本程序无法在二级目录下安装",
]);
switch ($_GET['action']) {
    case 'readme':
        ajaxout(1, "success", "", file_get_contents('data/readme.txt'));
        break;
    case 'dirs':
        $code = 1;
        $dirs = [
            [
                "name"  => "/app/open",
                "power" => getdirpower(PATH_APP . "open") ? 1 : 0,
            ], [
                "name"  => "/cache",
                "power" => getdirpower(PATH_CACHE) ? 1 : 0,
            ], [
                "name"  => "/core",
                "power" => getdirpower(PATH_CORE) ? 1 : 0,
            ], [
                "name"  => "/upload",
                "power" => getdirpower(PATH_UPLOAD) ? 1 : 0,
            ], [
                "name"  => "/public",
                "power" => getdirpower(PATH_PUBLIC) ? 1 : 0,
            ],
        ];
        foreach ($dirs as $key => $val) {
            if ($val['power'] != 1) {
                $code = 0;
                break;
            }
        }
        $serv = [
            [
                "name" => "系统信息",
                "desc" => php_uname('s'),
            ], [
                "name" => "环境信息",
                "desc" => $_SERVER["SERVER_SOFTWARE"],
            ],
        ];
        if (PHP_VERSION < "7.2.0") {
            $desc = "<span style='color:#F56C6C'><i class='layui-icon layui-icon-close'></i>请使用7.2.0及以上版本</span>";
            $code = 0;
        } else {
            $desc = "<span style='color:#67C23A'><i class='layui-icon layui-icon-ok'></i>版本可用 / 推荐8.0及其以上版本</span>";
        }
        $serv[] = [
            "name" => "PHP版本",
            "desc" => PHP_VERSION . " {$desc}",
        ];
        $desc = "";
        foreach ([
            "cURL"       => function_exists("curl_init"),
            "GD"         => function_exists("imagecreate"),
            "ZipArchive" => class_exists("ZipArchive"),
            "gzinflate"  => function_exists("gzinflate"),
            "PDO"        => class_exists("PDO"),
            "MySQLi"     => extension_loaded("mysqli"),
            "PDO_MySQL"  => extension_loaded("pdo_mysql"),
            "PDO_SQLite" => extension_loaded("pdo_sqlite"),
        ] as $name => $type) {
            if ($type) {
                $desc .= "<span style='color:#67C23A'>[<i class='layui-icon layui-icon-ok'></i>{$name}]</span>";
            } else {
                $desc .= "<a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AF{$name}%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:#F56C6C' title='点击搜索解决方案'>[<i class='layui-icon layui-icon-close'></i>{$name}]</a>";
                $code = 0;
            }
        }
        $serv[] = [
            "name" => "PHP扩展",
            "desc" => $desc,
        ];
        ajaxout($code, "success", "", [
            "server" => $serv,
            "dirs"   => $dirs,
        ]);
        break;
    case 'mysql':
        $db   = $_POST['db'];
        $mydb = new SQLPDO("mysql:host={$db['host']};port={$db['port']};charset=utf8mb4", $db['user'], $db['pass']);
        //创建数据库
        if (!$mydb->query("SHOW DATABASES LIKE '{$db['name']}'")) {
            $mydb->query("CREATE DATABASE {$db['name']} DEFAULT CHARACTER SET = utf8mb4 COLLATE utf8mb4_general_ci");
            $mydb->error() && ajaxout(0, $mydb->error());
        }
        $mydb = new SQLPDO("mysql:host={$db['host']};dbname={$db['name']};port={$db['port']};charset=utf8mb4", $db['user'], $db['pass']);
        //删除数据表
        foreach ($mydb->get_tables() as $table) {
            $mydb->query("DROP TABLE IF EXISTS {$table}");
            $mydb->error() && ajaxout(0, $mydb->error());
        }
        //导入数据表
        $sql = file_get_contents('data/data.sql');
        $sql = str_replace("[_PRE]", $db['pre'], $sql);
        $sql = str_replace("\r", "", $sql);
        $sql = explode(";\n", trim($sql));
        foreach ($sql as $key => $val) {
            $mydb->query("{$val};");
            $mydb->error() && ajaxout(0, $mydb->error());
        }
        $cfg = file_get_contents("data/config.php");
        $cfg || ajaxout(0, "配置文件读取失败");
        foreach ($db as $key => $val) {
            $cfg = str_replace("[db_{$key}]", $val, $cfg);
        }
        file_put_contents(PATH_CORE . "config.php", $cfg);
        if (file_get_contents(PATH_CORE . "config.php")) {
            ajaxout(1, "success");
        } else {
            ajaxout(0, "无法生成配置文件，请检查目录权限，或着关闭防篡改之类的插件！");
        }
        break;
    case 'admin':
        require_once PATH_CORE . "config.php";
        $admin = $_POST['admin'];
        if ($admin['pass'] != $admin['repass']) {
            ajaxout(0, "两次密码不一样");
        }
        $mydb = new SQLPDO("mysql:host={$_L['mysql']['host']};dbname={$_L['mysql']['name']};port={$_L['mysql']['port']};charset=utf8mb4", $_L['mysql']['user'], $_L['mysql']['pass']);
        $mydb->query("INSERT INTO `{$_L['mysql']['pre']}admin`(`id`,`tuid`,`status`,`name`,`title`,`pass`,`email`,`mobile`,`type`,`balance`,`addtime`,`lasttime`,`logintime`,`parameter`,`ip`,`lcms`) values (1,0,1,'{$admin['name']}','超级管理员','" . md5($admin['pass']) . "',NULL,NULL,'lcms','0.00','2019-01-01 00:00:00',NULL,NULL,'',NULL,0);");
        file_put_contents(PATH_CORE . "install.lock", date("Y-m-d H:i:s"));
        $mydb->error() || deldir(PATH_WEB . "install");
        ajaxout(1, "success");
        break;
}
