<?php
/* 数据库配置 */
$_L['mysql']['host']    = "[db_host]";
$_L['mysql']['port']    = [db_port];
$_L['mysql']['user']    = "[db_user]";
$_L['mysql']['pass']    = "[db_pass]";
$_L['mysql']['name']    = "[db_name]";
$_L['mysql']['pre']     = "[db_pre]";
$_L['mysql']['charset'] = "utf8mb4";

/* Redis配置 */
$_L['redis']['host'] = "127.0.0.1";
$_L['redis']['port'] = 6379;
$_L["redis"]["pass"] = "";

/* Memcached配置 */
$_L['memcached']['host']     = "127.0.0.1";
$_L['memcached']['port']     = 11211;
$_L['memcached']['pconnect'] = 1;
$_L['memcached']['timeout']  = 30;
$_L['memcached']['session']  = 1;

// 是否显示应用商店和更新 1 OR 0
$_L['developer']['appstore'] = 1;

// 后台显示字体，需要电脑上已安装此字体
$_L['developer']['fontfamily'] = "";