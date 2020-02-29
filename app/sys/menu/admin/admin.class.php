<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class admin extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function dosys()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'save':
                LCMS::config(array(
                    "do"    => "save",
                    "type"  => "sys",
                    "cate"  => "admin",
                    "unset" => "sys",
                    "lcms"  => true,
                ));
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            default:
                $type = "sys";
                $menu = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                $menus = array(
                    array(
                        "title" => "用户中心",
                        "type"  => "sys",
                        "menu"  => array("user"),
                    ),
                    array(
                        "title" => "设置",
                        "type"  => "sys",
                        "menu"  => array("config", "menu"),
                    ),
                );
                require LCMS::template("own/admin");
                break;
        }
    }
    public function select($menu)
    {
        global $_L;
        foreach ($menu as $key => $val) {
            $select[] = array(
                "title" => $val['title'],
                "value" => $key,
            );
        }
        return $select;
    }
    public function doopen()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'save':
                LCMS::config(array(
                    "do"    => "save",
                    "type"  => "sys",
                    "cate"  => "admin",
                    "unset" => "open",
                    "lcms"  => true,
                ));
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            default:
                $type = "open";
                $menu = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                $open  = traversal_one(PATH_APP . "open");
                $menus = array(
                    array(
                        "title" => "快捷菜单",
                        "type"  => "open",
                        "menu"  => array_values($open['dir']),
                    ),
                    array(
                        "title" => "应用中心",
                        "type"  => "sys",
                        "menu"  => array("appstore"),
                    ),
                );
                require LCMS::template("own/admin");
                break;
        }
    }
}
