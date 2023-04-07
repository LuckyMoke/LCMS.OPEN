<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:43:29
 * @LastEditTime: 2023-04-07 00:49:03
 * @Description: 数据表优化
 * @Copyright 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class optimize extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'list':
                $data = sql_query("SHOW TABLE STATUS ");
                $data = array_map(function ($table) {
                    return [
                        "Name"         => $table['Name'],
                        "Engine"       => $table['Engine'],
                        "Collation"    => $table['Collation'],
                        "Rows"         => $table['Rows'],
                        "Data_length"  => sprintf("%.2f", $table['Data_length'] / 1024) . "KB",
                        "Index_length" => sprintf("%.2f", $table['Index_length'] / 1024) . "KB",
                        "Data_free"    => $table['Data_free'],
                        "Comment"      => $table['Comment'],
                    ];
                }, $data);
                TABLE::$count = count($data);
                TABLE::out($data);
                break;
            case 'analyze':
            case 'optimize':
            case 'check':
            case 'repair':
            case 'truncate':
                set_time_limit(300);
                $LC || ajaxout(0, "请选择需要操作的表");
                foreach ($LC as $table) {
                    if ($table['Engine'] == "MyISAM") {
                        $info     = sql_query(strtoupper($LF['action']) . " TABLE `{$table['Name']}`");
                        $result[] = array_merge([
                            "Msg_type" => "ststus",
                            "Msg_text" => "OK",
                        ], $info ?: [], [
                            "Table" => $table['Name'],
                        ]);
                    }
                }
                $names = implode("、", array_column($LC, "Name"));
                LCMS::log([
                    "type" => "system",
                    "info" => "数据优化-{$LF['action']}-{$names}",
                ]);
                ajaxout(2, "数据表操作完成", "showResult", $result ?: []);
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "limit"   => count($_L['table']),
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 50],
                        ["title" => "表名", "field" => "Name",
                            "width"  => 300],
                        ["title" => "表类型", "field" => "Engine",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title" => "排序规则", "field" => "Collation",
                            "width"  => 180,
                            "align"  => "center"],
                        ["title" => "记录数", "field" => "Rows",
                            "width"  => 150,
                            "align"  => "center"],
                        ["title" => "数据大小", "field" => "Data_length",
                            "width"  => 150,
                            "align"  => "center"],
                        ["title" => "索引大小", "field" => "Index_length",
                            "width"  => 150,
                            "align"  => "center"],
                        ["title" => "数据碎片", "field" => "Data_free",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title"   => "注释", "field" => "Comment",
                            "minWidth" => 150],
                    ],
                    "toolbar" => [
                        ["title"  => "分析表", "event" => "ajax",
                            "url"     => "index&action=analyze",
                            "timeout" => 0,
                            "tips"    => "确认分析数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "优化表", "event" => "ajax",
                            "url"     => "index&action=optimize",
                            "timeout" => 0,
                            "tips"    => "确认优化数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "检查表", "event" => "ajax",
                            "url"     => "index&action=check",
                            "timeout" => 0,
                            "tips"    => "确认检查数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "修复表", "event" => "ajax",
                            "url"     => "index&action=repair",
                            "color"   => "warm",
                            "timeout" => 0,
                            "tips"    => "确认修复数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "清空表", "event" => "ajax",
                            "url"     => "index&action=truncate",
                            "color"   => "danger",
                            "timeout" => 0,
                            "tips"    => "确认清空数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                    ],
                ];
                require LCMS::template("own/optimize/index");
                break;
        }
    }
}
