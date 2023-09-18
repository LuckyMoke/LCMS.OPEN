<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:14
 * @LastEditTime: 2023-09-14 19:38:49
 * @Description: 欢迎页
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('level');
class index extends adminbase
{
    public function __construct()
    {
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        if (!$_L['config']['web']['domain']) {
            LCMS::X(400, "检测到您是第一次安装使用<br/>请先到 框架设置 填写默认域名", [[
                "title" => "框架设置",
                "color" => "danger",
                "url"   => "index.php?t=sys&n=config&c=admin&a=web",
            ]]);
        }
        $open = LEVEL::applist("open", true, 12);
        $info = server_info();
        if (in_string(strtoupper($info['sys']), "IIS")) {
            $info['sys'] = "<span style='color:red'>{$info['sys']} (请使用Apache/Nginx环境)</span>";
        }
        $info = array_merge($info, [
            "mysql" => [
                "master" => $_L['DB']->assign("master")->version(),
                "slave"  => $_L['DB']->assign("slave")->version(),
            ],
        ]);
        if ($_L['LCMSADMIN']['lasttime'] && $_L['LCMSADMIN']['lasttime'] > "0000-00-00 00:00:00") {
            $lasttime = (strtotime($_L['LCMSADMIN']['lasttime']) - time()) / 86400;
        }
        if (LCMS::SUPER() && $_L['developer']['appstore'] !== 0) {
            $update = 1;
        }
        require LCMS::template("own/index");
    }
};
