<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-09-28 14:32:25
 * @LastEditTime: 2024-01-17 11:00:58
 * @Description: 文件管理
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class files extends adminbase
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
        switch ($LF['action']) {
            case 'list':
                $doamin = $_L['plugin']['oss']['type'] != "local" ? $_L['plugin']['oss']['domain'] : $_L['url']['site'];
                $where  = $LC['name'] ? " AND name LIKE :name" : "";
                $where .= $LC['type'] ? " AND type = :type" : "";
                $data = TABLE::set("upload", "lcms = :lcms AND uid = :uid{$where}", "id DESC", [
                    ":lcms" => $_L['ROOTID'],
                    ":uid"  => $_L['LCMSADMIN']['id'],
                    ":name" => "%{$LC['name']}%",
                    ":type" => $LC['type'],
                ]);
                foreach ($data as $index => $val) {
                    $src          = str_replace("../", "", $val['src']);
                    $data[$index] = array_merge($val, [
                        "type"  => $val['type'] === "file" ? "文件" : "图片",
                        "size"  => getunit($val['size']),
                        "oname" => $val['oname'] ?: '<span style="color:#cccccc">无</span>',
                        "href"  => [
                            "type"   => "link",
                            "title"  => "/{$src}",
                            "url"    => ($val['local'] == 1 ? $_L['url']['site'] : $doamin) . $src,
                            "target" => "_blank",
                        ],
                    ]);
                }
                TABLE::out($data);
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["type" => "checkbox", "width" => 50],
                        ["title" => "ID", "field" => "id",
                            "width"  => 80,
                            "align"  => "center"],
                        ["title" => "文件类型", "field" => "type",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title" => "文件目录", "field" => "datey",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title" => "文件大小", "field" => "size",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title" => "存储文件名", "field" => "name",
                            "width"  => 180],
                        ["title" => "文件链接", "field" => "href",
                            "width"  => 370],
                        ["title"   => "原始文件名", "field" => "oname",
                            "minWidth" => 200],
                        ["title" => "上传时间", "field" => "addtime",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 70,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title" => "删除", "event" => "ajax",
                                    "url"    => "{$_L['url']['own_form']}delimg&n=upload&c=index",
                                    "color"  => "danger",
                                    "tips"   => "确认删除？无法恢复！"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => "批量删除", "event" => "ajax",
                            "url"    => "{$_L['url']['own_form']}delimg&n=upload&c=index",
                            "color"  => "danger",
                            "tips"   => "确认删除？无法恢复！"],
                    ],
                    "search"  => [
                        ["title" => "文件类型", "name" => "type",
                            "type"   => "select",
                            "option" => [
                                ["title" => "文件",
                                    "value"  => "file"],
                                ["title" => "图片",
                                    "value"  => "image"],
                            ]],
                        ["title" => "存储文件名", "name" => "name"],
                    ],
                ];
                require LCMS::template("own/files/index");
                break;
        }
    }
}
