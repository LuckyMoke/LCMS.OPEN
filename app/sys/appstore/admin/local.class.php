<?php
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
    public function douninstall()
    {
        global $_L;
        $dir = PATH_APP . "open/{$_L['form']['app']}/";
        if (is_file($dir . "uninstall.sql")) {
            $this->updatesql(file_get_contents($dir . "uninstall.sql"));
        }
        deldir($dir);
        ajaxout(1, 'success');
    }
    /**
     * [updatesql 更新数据库]
     * @param  [type] $sqldata [description]
     * @return [type]          [description]
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
