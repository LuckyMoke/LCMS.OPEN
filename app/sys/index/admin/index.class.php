<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:14
 * @LastEditTime: 2022-03-19 19:46:21
 * @Description: 框架菜单处理
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $app    = [];
        $config = LCMS::config([
            "type" => "sys",
            "name" => "menu",
            "cate" => "admin",
        ]);
        $config['sys'] = [
            ["title" => "用户中心", "menu" => [
                "user" => [
                    "class" => [
                        "admin" => 1,
                    ],
                ],
            ]],
            ["title" => "框架配置", "menu" => [
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
                        "files"    => 1,
                    ],
                ],
                "update" => [
                    "class" => [
                        "gitee" => 1,
                    ],
                ],
            ]],
        ];
        $config['open'][1] = [
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
        foreach ($config['sys'] as $index => $list) {
            foreach ($list['menu'] as $name => $li) {
                foreach ($li['class'] as $class => $type) {
                    if ($type) {
                        $tempsys[$index]['title']         = $list['title'];
                        $tempsys[$index]['menu'][$name][] = $class;
                    }
                }
            }
        }
        foreach ($tempsys as $index => $list) {
            $sys[$index]['title'] = $list['title'];
            if (count($list['menu']) > 1) {
                foreach ($list['menu'] as $name => $li) {
                    $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
                    if ($app[$name]['menu']) {
                        $sys[$index]['menu'][$name] = [
                            "title" => $app[$name]['info']['title'],
                            "url"   => $app[$name]['url']['all'],
                        ];
                        foreach ($li as $class) {
                            if ($app[$name]['menu'][$class]) {
                                $sys[$index]['menu'][$name]['menu'][] = [
                                    "title" => $app[$name]['menu'][$class]['title'],
                                    "url"   => $app[$name]['url'][$class],
                                ];

                            }
                        }
                    }
                }
            } else {
                $name       = array_key_first($list['menu']);
                $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
                foreach ($list['menu'][$name] as $class) {
                    if ($app[$name]['menu'][$class]) {
                        $sys[$index]['menu'][] = array(
                            "title" => $app[$name]['menu'][$class]['title'],
                            "url"   => $app[$name]['url'][$class],
                        );
                    }
                }
            }
        }
        $tempopen[0]['title'] = "快捷菜单";
        foreach ($config['open'] as $index => $list) {
            foreach ($list['menu'] as $name => $cls) {
                if ($name == "appstore") {
                    if (!LCMS::SUPER()) {
                        unset($cls['class']['store']);
                    }
                    if ($_L['developer'] && $_L['developer']['appstore'] === 0) {
                        unset($cls['class']['store']);
                    }
                }
                if (is_array($cls)) {
                    foreach ($cls['class'] as $cname => $type) {
                        if ($type) {
                            $tempopen[$index]['title']         = $list['title'];
                            $tempopen[$index]['menu'][$name][] = $cname;
                        }
                    }
                } elseif ($cls) {
                    $tempopen[$index]['title']       = $list['title'];
                    $tempopen[$index]['menu'][$name] = 1;
                }
            }
        }
        foreach ($tempopen as $index => $list) {
            $open[$index]['title'] = $list['title'];
            if (count((array) $list['menu']) > 1) {
                foreach ($list['menu'] as $name => $li) {
                    $app[$name] = $app[$name] ?: LEVEL::app($name, $name == "appstore" ? "sys" : "open");
                    if ($app[$name]['menu']) {
                        $open[$index]['menu'][$name] = [
                            "title" => $app[$name]['info']['title'],
                            "url"   => $app[$name]['url']['all'],
                        ];
                        foreach ($li as $class) {
                            if ($app[$name]['menu'][$class]) {
                                $open[$index]['menu'][$name]['menu'][] = [
                                    "title" => $app[$name]['menu'][$class]['title'],
                                    "url"   => $app[$name]['url'][$class],
                                ];

                            }
                        }
                    }
                }
            } else {
                !empty($list['menu']) && $name = array_key_first($list['menu']);
                $app[$name]                    = $app[$name] ?: LEVEL::app($name, $name == "appstore" ? "sys" : "open");
                if (is_array($list['menu'][$name])) {
                    foreach ($list['menu'][$name] as $class) {
                        if ($app[$name]['menu'][$class]) {
                            $open[$index]['menu'][] = array(
                                "title" => $app[$name]['menu'][$class]['title'],
                                "url"   => $app[$name]['url'][$class],
                            );
                        }
                    }
                } elseif ($list['menu'][$name]) {
                    $open[$index]['menu'][$name] = [
                        "title" => $app[$name]['info']['title'],
                        "url"   => $app[$name]['url']['all'],
                    ];
                }
            }
        }
        require LCMS::template("own/index");
    }
};
