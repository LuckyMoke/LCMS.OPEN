<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-03-07 15:50:06
 * @LastEditTime: 2024-04-17 14:01:57
 * @Description: Index页面
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('adminbase');
LOAD::sys_class('table');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        $app    = [];
        $sys    = [];
        $open   = [];
        $config = LCMS::config([
            "type" => "sys",
            "name" => "menu",
            "cate" => "admin",
        ]);
        $config['sys'] = [
            "appstore" => [
                "local" => 1,
                "store" => 1,
            ],
            "user"     => [
                "admin" => 1,
            ],
            "config"   => [
                "admin" => 1,
                "web"   => 1,
            ],
            "backup"   => [
                "database" => 1,
                "optimize" => 1,
                "files"    => 1,
            ],
            "update"   => [
                "gitee" => 1,
            ],
        ];
        if (LCMS::SUPER() && $_L['developer']['appstore'] !== 0) {
            $update = 1;
        } else {
            unset($config['sys']['update']);
        }
        foreach ($config['sys'] as $name => $class) {
            $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
            if ($app[$name]['menu']) {
                $sys[] = [
                    "name"  => $name,
                    "icon"  => $app[$name]['info']['icon'],
                    "title" => $app[$name]['info']['title'],
                    "url"   => $app[$name]['url']['all'],
                ];
            }
        }
        foreach ($config['open'][0]['menu'] as $name => $on) {
            if ($on != 1) {
                continue;
            }
            $app[$name] = $app[$name] ?: LEVEL::app($name, "open");
            if ($app[$name]['menu']) {
                $open[] = [
                    "name"  => $name,
                    "icon"  => "",
                    "title" => $app[$name]['info']['title'],
                    "url"   => $app[$name]['url']['all'],
                ];
            }
        }
        $applist = LEVEL::applist("open", true, 6);
        if ($config['default'] && $applist) {
            switch ($config['default']['on']) {
                case '1':
                    $homeurl = reset($applist)['url'];
                    break;
                case '2':
                    $homeurl = $applist[$config['default']['name']]['url'];
                    break;
            }
        }
        $homeurl = $homeurl ?: "{$_L['url']['admin']}index.php?n=home";
        require LCMS::template("own/index");
    }
    public function donotify()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'list':
                $data = TABLE::set("notify", "lcms = :lcms", "id DESC", [
                    ":lcms" => $_L['ROOTID'],
                ], "id, title, isread, addtime, lcms");
                foreach ($data as $index => $val) {
                    $data[$index] = array_merge($val, [
                        "title"  => [
                            "type"  => "link",
                            "url"   => "javascript:openNotify({$val['id']})",
                            "title" => $val["title"],
                        ],
                        "isread" => $val["isread"] == 1 ? "已读" : '<span style="color:red">未读</span>',
                    ]);
                }
                TABLE::out($data);
                break;
            case 'list-del':
                if (TABLE::del("notify")) {
                    ajaxout(1, "删除成功", "reload");
                } else {
                    ajaxout(0, "删除失败");
                }
                break;
            case 'readall':
                sql_update([
                    "table" => "notify",
                    "data"  => [
                        "isread" => 1,
                    ],
                    "where" => "lcms = :lcms",
                    "bind"  => [
                        ":lcms" => $_L['ROOTID'],
                    ],
                ]);
                ajaxout(1, "设置成功", "reload");
                break;
            case 'show':
                $data = LCMS::form([
                    "do"    => "get",
                    "table" => "notify",
                    "id"    => $LF['id'],
                ]);
                if ($data) {
                    if ($data['isread'] == 0) {
                        LCMS::form([
                            "do"    => "save",
                            "table" => "notify",
                            "form"  => [
                                "isread" => 1,
                            ],
                            "id"    => $data['id'],
                        ]);
                    }
                    require LCMS::template("own/notify-show");
                } else {
                    LCMS::X(403, "未找到通知数据");
                }
                break;
            default:
                $table = [
                    "url"     => "notify&action=list",
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 50],
                        ["title" => "ID", "field" => "id",
                            "width"  => 70,
                            "align"  => "center"],
                        ["title" => "状态", "field" => "isread",
                            "width"  => 70,
                            "align"  => "center"],
                        ["title"   => "标题", "field" => "title",
                            "minWidth" => 300],
                        ["title" => "时间", "field" => "addtime",
                            "width"  => 170,
                            "align"  => "center"],
                    ],
                    "toolbar" => [
                        ["title" => "全部已读", "event" => "ajax",
                            "url"    => "notify&action=readall",
                            "color"  => "default",
                            "tips"   => "确认设置全部已读？"],
                        ["title" => "批量删除", "event" => "ajax",
                            "url"    => "notify&action=list-del",
                            "color"  => "danger",
                            "tips"   => "确认删除（不可恢复）？"],
                    ],
                ];
                require LCMS::template("own/notify");
                break;
        }
    }
    public function doheart()
    {
        global $_L, $LF, $LC;
        if ($_L['table']['notify']) {
            $count = sql_counter([
                "table" => "notify",
                "where" => "isread = 0 AND lcms = :lcms",
                "bind"  => [
                    ":lcms" => $_L['ROOTID'],
                ],
            ]);
            ajaxout(1, "success", "", [
                "notify" => [
                    "count" => $count > 99 ? 99 : $count,
                    "list"  => sql_getall([
                        "table"  => "notify",
                        "where"  => "isread = 0 AND lcms = :lcms",
                        "order"  => "id DESC",
                        "bind"   => [
                            ":lcms" => $_L['ROOTID'],
                        ],
                        "fields" => "id, title, isread, addtime, lcms",
                        "limit"  => 10,
                    ]),
                ],
            ]);
        }
        ajaxout(1, "success");
    }
};
