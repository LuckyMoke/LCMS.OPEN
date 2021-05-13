<?php
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
            LCMS::X(400, "检测到您是第一次打开后台<br/>请先到 <a href='index.php?t=sys&n=config&c=admin&a=web' style='color:red'>框架设置</a> 填写默认域名");
        }
        $info   = server_info();
        $level  = level::app('config');
        $update = $level['url']['update'] ? "1" : "0";
        if ($_L['LCMSADMIN']['lasttime'] && $_L['LCMSADMIN']['lasttime'] > "0000-00-00 00:00:00") {
            $lasttime = (strtotime($_L['LCMSADMIN']['lasttime']) - time()) / 86400;
        }
        require LCMS::template("own/index");
    }
};
