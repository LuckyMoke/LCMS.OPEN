<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2020-11-20 14:25:28
 * @Description: 常用应用设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LC;
        parent::__construct();
        $LC = $_L['form']['LC'];
    }
    public function doindex()
    {
        global $_L, $LC;
        switch ($_L['form']['action']) {
            case 'save':
                $_L['form']['LC']['open'][1] = [
                    "title" => "应用中心",
                    "menu"  => [
                        "appstore" => [
                            "class" => [
                                "local" => 1,
                                "store" => 1,
                            ],
                        ],
                    ],
                ];
                if (LCMS::SUPER()) {
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
                            "title" => "框架设置",
                            "menu"  => [
                                "config" => [
                                    "class" => [
                                        "admin"  => 1,
                                        "web"    => 1,
                                        "update" => 1,
                                    ],
                                ],
                                "backup" => [
                                    "class" => [
                                        "database" => 1,
                                        "optimize" => 1,
                                    ],
                                ],
                            ],
                        ],
                    ];
                }
                LCMS::config([
                    "do"    => "save",
                    "type"  => "sys",
                    "cate"  => "admin",
                    "unset" => "sys|open",
                    "lcms"  => LCMS::SUPER() ? true : "",
                ]);
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    ajaxout(1, "保存成功", "reload-top");
                }
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => LCMS::SUPER() ? true : "",
                ])['open'][0]['menu'];
                $open = traversal_one(PATH_APP . "open")['dir'];
                if ($open == ["system"]) {
                    LCMS::X(404, "未找到可以使用的应用");
                }
                if (LCMS::SUPER()) {
                    foreach ($open as $app) {
                        $info = LEVEL::app($app, "open")['info'];
                        if ($info) {
                            $applist[$app] = $info;
                        }
                    }
                } else {
                    $applist = $_L['LCMSADMIN']['level']['open'];
                    foreach ($applist as $app => $cls) {
                        if (is_array($cls) && in_array($app, $open, true)) {
                            foreach ($cls as $cname => $func) {
                                if (is_array($func)) {
                                    foreach ($func as $fname => $on) {
                                        if ($on != 1) {
                                            unset($func[$fname]);
                                        }
                                    }
                                }
                                $cls[$cname] = $func;
                                if (count($cls[$cname]) <= 0) {
                                    unset($cls[$cname]);
                                }
                            }
                            if (count($cls) <= 0) {
                                unset($applist[$app]);
                            }
                        } else {
                            unset($applist[$app]);
                        }
                    }
                    foreach ($applist as $app => $val) {
                        $info = LEVEL::app($app, "open")['info'];
                        if ($info) {
                            $applist[$app] = $info;
                        }
                    }
                }
                $config = array_merge($config ?: [], array_diff_key($applist, $config ?: []));
                foreach ($config as $name => $on) {
                    if (is_array($on)) {
                        $list[$name]       = $applist[$name];
                        $list[$name]['on'] = 0;
                    } elseif ($applist[$name]) {
                        $list[$name] = array_merge([
                            "on" => $on,
                        ], $applist[$name]);
                    }
                }
                require LCMS::template("own/index");
                break;
        }
    }
}
