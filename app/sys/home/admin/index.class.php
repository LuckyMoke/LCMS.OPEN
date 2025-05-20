<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:14
 * @LastEditTime: 2025-05-09 11:21:28
 * @Description: 欢迎页
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('adminbase');
LOAD::sys_class('level');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $ACFG;
        parent::__construct();
        $ACFG = $_L['config']['admin'];
    }
    public function doindex()
    {
        global $_L, $ACFG;
        if (!$_L['config']['web']['domain']) {
            LCMS::X(400, "检测到您是第一次安装使用<br/>请先到 框架设置 填写默认域名", [[
                "title" => "框架设置",
                "color" => "danger",
                "url"   => "index.php?t=sys&n=config&c=admin&a=web",
            ]]);
        }
        if (LCMS::SUPER()) {
            $app_host = $ACFG['app_host'] ?: [];
            if (
                !$app_host ||
                !in_array(HTTP_HOST, $app_host)
            ) {
                array_push($app_host, HTTP_HOST);
                $app_host = array_unique($app_host);
                $app_host = array_values($app_host);
                LCMS::config([
                    "do"    => "save",
                    "name"  => "config",
                    "type"  => "sys",
                    "cate"  => "admin",
                    "lcms"  => true,
                    "unset" => "app_host",
                    "form"  => [
                        "app_host" => $app_host,
                    ],
                ]);
            }
        }
        require LCMS::template("own/index");
    }
    public function dodata()
    {
        global $_L, $ACFG;
        if (LCMS::SUPER()) {
            if ($ACFG['dir'] == "admin") {
                $tips[] = "系统提示：检测到您的后台目录为默认的 admin ，为了后台安全，可在<a href=\"javascript:LCMS.plugin.router(LCMS.url.admin+`index.php?t=sys&n=config&c=admin&a=safe`)\">“设置->安全性能->后台目录</a>中修改，以提高安全性！";
            }
            if (is_dir(PATH_WEB . "install")) {
                $tips[] = "系统提示：检测到您未删除安装目录，请尽快删除<code>/install</code>目录，以提高安全性！";
            }
            if (is_file(PATH_WEB . "install.php")) {
                $tips[] = "系统提示：检测到您未删除安装文件，请尽快删除<code>/install.php</code>文件，以提高安全性！";
            }
        } else {
            if (
                $_L['LCMSADMIN']['lasttime'] &&
                $_L['LCMSADMIN']['lasttime'] > "0000-00-00 00:00:00"
            ) {
                $lasttime = (strtotime($_L['LCMSADMIN']['lasttime']) - time()) / 86400;
                if ($lasttime && $lasttime < 15) {
                    $tips[] = "系统提示：您的账号将于 {{$_L['LCMSADMIN']['lasttime']}} 到期，为避免影响使用，请及时处理！";
                }
            }
        }
        $info = server_info();
        if (in_string(strtoupper($info['sys']), "IIS")) {
            $info['sys'] = "<span style='color:red'>{$info['sys']} (请使用Apache或Nginx)</span>";
        }
        $info = array_merge($info, [
            "mysql" => [
                "master" => $_L['DB']->assign("master")->version(),
                "slave"  => $_L['DB']->assign("slave")->version(),
            ],
        ]);
        if (version_compare($info['php'], "7.3", "lt")) {
            $phpban = true;
        }
        $php = $info['php'];
        if (version_compare($php, "8.2", "lt")) {
            $php .= " (推荐PHP8.2及以上)";
        }
        $php = "PHP/{$php} 主数据库/{$info['mysql']['master']}";
        if ($info['mysql']['slave']) {
            $php .= "从数据库/{$info['mysql']['slave']}";
        }
        if (
            LCMS::SUPER() &&
            $_L['developer']['appstore'] !== 0
        ) {
            $update = true;
        }
        ajaxout(1, "success", "", [
            "tips"    => $tips ?: [],
            "apps"    => LEVEL::applist("open", true, 12),
            "info"    => [
                "服务器系统"   => $info['os'],
                "服务器环境"   => $info['sys'],
                "运行环境"    => $php,
                "Opcache" => $info['opcache'] ?? "未开启opcache扩展，开启有助于降低CPU使用率，但会增加内存使用量",
                "PHP扩展"   => $this->getComs(),
                "开源组件"    => "Layui、Amazeui、Neditor、FontAwesome、霞鹜尚智黑、Gantari、Alpine.js",
            ],
            "update"  => $update ? true : false,
            "gonggao" => $ACFG['gonggao'] ? html_editor($ACFG['gonggao']) : null,
            "phpban"  => $phpban ? $info['php'] : false,
        ]);
    }
    private function getComs()
    {
        $coms = "";
        foreach ([
            "curl"       => extension_loaded("curl"),
            "gd"         => extension_loaded("gd"),
            "zip"        => extension_loaded("zip"),
            "mbstring"   => extension_loaded("mbstring"),
            "zlib"       => extension_loaded("zlib"),
            "pdo_mysql"  => extension_loaded("pdo_mysql"),
            "pdo_sqlite" => extension_loaded("pdo_sqlite"),
        ] as $name => $on) {
            $color = $on ? "green" : "red";
            $coms .= ($name != "curl" ? "/" : "") . "<span style=\"padding-right:2px;color:{$color}\">{$name}</span>";
        }
        $coms .= " [可选:";
        foreach ([
            "fileinfo" => extension_loaded("fileinfo"),
            "redis"    => extension_loaded("redis"),
        ] as $name => $on) {
            $color = $on ? "green" : "red";
            $coms .= ($name != "fileinfo" ? "/" : "") . "<span style=\"padding-right:2px;color:{$color}\">{$name}</span>";
        }
        $coms .= "]";
        return $coms;
    }
};
