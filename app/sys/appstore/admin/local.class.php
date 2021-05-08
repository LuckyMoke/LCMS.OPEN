<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-05-06 12:54:45
 * @Description:本地应用列表
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class local extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $level   = level::app('appstore');
        $applist = level::appall();
        foreach ($applist['open'] as $name => $list) {
            if (count($list['menu']) > 1) {
                $open[$name] = $applist['open'][$name]['url']['all'];
            } elseif ($list['menu']) {
                $open[$name] = $applist['open'][$name]['url']['all'];
            }
        }
        require LCMS::template("own/local/index");
    }
    /**
     * @description: 卸载应用
     * @param {*}
     * @return {*}
     */
    public function douninstall()
    {
        global $_L;
        $dir = PATH_APP . "open/{$_L['form']['app']}/";
        if (is_file($dir . "uninstall.sql")) {
            $this->updatesql(str_replace("[TABLE_PRE]", $_L['mysql']['pre'], file_get_contents($dir . "uninstall.sql")));
        }
        deldir($dir);
        ajaxout(1, 'success');
    }
    /**
     * @description: 更新数据库
     * @param string $sqldata
     * @return {*}
     */
    private function updatesql($sqldata)
    {
        global $_L;
        $sqldata = str_replace("\r", "", $sqldata);
        $sqldata = explode(";\n", trim($sqldata));
        foreach ($sqldata as $val) {
            if ($val) {
                sql_query($val);
            }
        }
        return true;
    }
}
