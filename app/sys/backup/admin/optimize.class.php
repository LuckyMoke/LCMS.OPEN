<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:43:29
 * @LastEditTime: 2024-11-15 11:47:30
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
                        "Data_length"  => getunit($table['Data_length']),
                        "Index_length" => getunit($table['Index_length']),
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
                set_time_limit(300);
                $LC || ajaxout(0, "请选择需要操作的表");
                $names = [];
                foreach ($LC as $table) {
                    if ($table['Engine'] == "MyISAM" || $LF['action'] == "truncate") {
                        $info     = sql_query(strtoupper($LF['action']) . " TABLE `{$table['Name']}`");
                        $result[] = array_merge([
                            "Msg_type" => "status",
                            "Msg_text" => "OK",
                        ], $info ?: [], [
                            "Table" => $table['Name'],
                        ]);
                        $names[] = $table['Name'];
                    }
                }
                $names = implode("、", $names);
                $names && LCMS::log([
                    "type" => "system",
                    "info" => "数据优化-{$LF['action']}-{$names}",
                ]);
                ajaxout(2, "数据表操作完成", "showResult", $result ?: [[
                    "Table"    => "操作完成",
                    "Msg_type" => "status",
                    "Msg_text" => "OK",
                ]]);
                break;
            case 'alter':
                set_time_limit(300);
                $LC || ajaxout(0, "请选择需要操作的表");
                $names = [];
                foreach ($LC as $table) {
                    if ($table['Engine'] == "MyISAM") {
                        sql_query("ALTER TABLE `{$table['Name']}` ENGINE=InnoDB");
                    } elseif ($table['Engine'] == "InnoDB") {
                        sql_query("ALTER TABLE `{$table['Name']}` ENGINE=MyISAM");
                    }
                    $result[] = array_merge([
                        "Table"    => $table['Name'],
                        "Msg_type" => sql_error() ? "error" : "status",
                        "Msg_text" => sql_error() ?: "OK",
                    ]);
                    $names[] = $table['Name'];
                }
                $names = implode("、", $names);
                $names && LCMS::log([
                    "type" => "system",
                    "info" => "数据优化-{$LF['action']}-{$names}",
                ]);
                ajaxout(2, "数据表操作完成", "showResult", $result ?: [[
                    "Table"    => "操作完成",
                    "Msg_type" => "status",
                    "Msg_text" => "OK",
                ]]);
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "limit"   => 500,
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
                        ["title"  => "分析", "event" => "ajax",
                            "url"     => "index&action=analyze",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认分析数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "优化", "event" => "ajax",
                            "url"     => "index&action=optimize",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认优化数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "检查", "event" => "ajax",
                            "url"     => "index&action=check",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认检查数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "转换", "event" => "ajax",
                            "url"     => "index&action=alter",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认转换数据表？表类型会在InnoDB与MyISAM之间互相转换！<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                    ],
                ];
                if ($_L['developer']['lite'] === 1) {
                    unset($table['toolbar']);
                }
                require LCMS::template("own/optimize/index");
                break;
        }
    }
}
