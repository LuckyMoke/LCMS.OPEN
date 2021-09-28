<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-09-28 14:32:25
 * @LastEditTime: 2021-09-28 15:25:11
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
                    $src          = str_replace("..", "", $val['src']);
                    $data[$index] = array_merge($val, [
                        "type" => $val['type'] == "file" ? "文件" : "图片",
                        "size" => $this->getsize($val['size']),
                        "href"  => "<a href='{$doamin}{$src}' target='_blank'><i class='layui-icon layui-icon-unlink'></i> {$src}</a>",
                    ]);
                }
                TABLE::out($data);
                break;
            default:
                $table = [
                    "url"    => "index&action=list",
                    "cols"   => [
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
                        ["title"   => "文件链接", "field" => "href",
                            "minWidth" => 300],
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
                                    "tips"   => "确认删除？"],
                            ]],
                    ],
                    "search" => [
                        ["title" => "文件类型", "name" => "type",
                            "type"   => "select",
                            "option" => [
                                ["title" => "文件",
                                    "value"  => "file"],
                                ["title" => "图片",
                                    "value"  => "image"],
                            ]],
                        ["title" => "文件名称", "name" => "name"],
                    ],
                ];
                require LCMS::template("own/files/index");
                break;
        }
    }
    /**
     * @description: 字节大小转换
     * @param int $size
     * @return string
     */
    public function getsize($size = 0)
    {
        if ($size >= 1073741824) {
            $unit = "GB";
        } elseif ($size >= 1048576) {
            $unit = "MB";
        } elseif ($size >= 1024) {
            $unit = "KB";
        } else {
            $unit = "B";
        }
        switch ($unit) {
            case 'GB':
                $size = $size / 1073741824;
                $size = sprintf("%.2f", $size);
                break;
            case 'MB':
                $size = $size / 1048576;
                $size = sprintf("%.2f", $size);
                break;
            case 'B':
                $size = sprintf("%.2f", $size);
                break;
            default:
                $size = $size / 1024;
                $size = sprintf("%.2f", $size);
                break;
        }
        return $size . $unit;
    }
}
