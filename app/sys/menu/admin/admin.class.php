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
    public function doopen()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'save':
                $_L['form']['LC']['sys'] = [
                    [
                        "title" => "用户中心",
                        "menu"  => [
                            "user" => [
                                "class" => [
                                    "admin" => 1,
                                ],
                            ],
                        ],
                    ],
                    [
                        "title" => "设置中心",
                        "menu"  => [
                            "config" => [
                                "class" => [
                                    "admin"  => 1,
                                    "web"    => 1,
                                    "update" => 1,
                                ],
                            ],
                            "menu"   => [
                                "class" => [
                                    "admin" => 1,
                                ],
                            ],
                        ],
                    ],
                ];
                $_L['form']['LC']['open'][1] = [
                    "title" => "应用中心",
                    "menu" => [
                        "appstore" => [
                            "class" => [
                                "local" => 1,
                                "store" => 1,
                            ],
                        ],
                    ],
                ];
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
                $open = traversal_one(PATH_APP . "open");
                if ($open['dir'] == ["system"]) {
                    LCMS::X(404, "没有应用需要设置菜单");
                }
                $menus = [
                    [
                        "title" => "快捷菜单",
                        "type"  => "open",
                        "menu"  => array_values($open['dir']),
                    ],
                ];
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
}
