<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:43:29
 * @LastEditTime: 2026-10-26 10:23:30
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
                        "Engine"       => $this->checkRepair($table['Engine']),
                        "Collation"    => $this->checkRepair($table['Collation']),
                        "Rows"         => $this->checkRepair($table['Rows']),
                        "Data_length"  => getunit($table['Data_length']),
                        "Index_length" => getunit($table['Index_length']),
                        "Data_free"    => getunit($table['Data_free']),
                        "Comment"      => htmlspecialchars((string) $table['Comment'], ENT_QUOTES),
                    ];
                }, $data);
                TABLE::$count = count($data);
                TABLE::out($data);
                break;
            case 'analyze':
            case 'optimize':
            case 'check':
            case 'repair':
                if (function_exists('set_time_limit')) {
                    @set_time_limit(300);
                }
                is_array($LC) || ajaxout(0, "请选择需要操作的表");
                $action = strtoupper($LF['action']);
                $result = $this->tableAction($action, $LC);
                $result['list'] || ajaxout(0, "没有可操作的数据表");
                $result['done'] && LCMS::log([
                    "type" => "system",
                    "info" => "数据优化：{$LF['action']}/" . implode("、", $result['done']),
                ]);
                ajaxout(2, "数据表操作完成", "showResult", $result['list']);
                break;
            case 'alter':
                if (function_exists('set_time_limit')) {
                    @set_time_limit(300);
                }
                is_array($LC) || ajaxout(0, "请选择需要操作的表");
                $status = $this->tableStatus();
                $status || ajaxout(0, "获取数据表列表失败");
                $result = [];
                $names  = [];
                foreach ($LC as $table) {
                    if (!is_array($table)) continue;
                    $name = trim((string) ($table['Name'] ?? ""));
                    if ($name === "" || !array_key_exists($name, $status)) continue;
                    $engine = $status[$name];
                    if ($engine === "MyISAM") {
                        sql_query("ALTER TABLE `{$name}` ENGINE=InnoDB");
                    } elseif ($engine === "InnoDB") {
                        sql_query("ALTER TABLE `{$name}` ENGINE=MyISAM");
                    } else {
                        $result[] = [
                            "Table"    => $name,
                            "Msg_type" => "warning",
                            "Msg_text" => "表类型不支持转换，已跳过",
                        ];
                        continue;
                    }
                    $result[] = [
                        "Table"    => $name,
                        "Msg_type" => sql_error() ? "error" : "status",
                        "Msg_text" => sql_error() ?: "OK",
                    ];
                    $names[] = $name;
                }
                $result || ajaxout(0, "没有可操作的数据表");
                $names  = implode("、", $names);
                $names && LCMS::log([
                    "type" => "system",
                    "info" => "数据优化：{$LF['action']}/{$names}",
                ]);
                ajaxout(2, "数据表操作完成", "showResult", $result);
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
                        ["title"  => "修复", "event" => "ajax",
                            "url"     => "index&action=repair",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认修复数据表？<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                        ["title"  => "转换", "event" => "ajax",
                            "url"     => "index&action=alter",
                            "color"   => "primary",
                            "timeout" => 0,
                            "tips"    => "确认转换数据表？表类型会在InnoDB与MyISAM之间互相转换！<span style=\"color:red\">请先做好数据库备份！！！</span>"],
                    ],
                    "done"    => "checkRepair",
                ];
                if ($_L['developer']['lite'] === 1) {
                    unset($table['toolbar']);
                }
                require LCMS::template("own/optimize/index");
                break;
        }
    }
    /**
     * @description: 执行表操作（分析/优化/检查/修复）
     * @param string $action ANALYZE|OPTIMIZE|CHECK|REPAIR
     * @param array $tables 前端提交的表数据
     * @return array [list => 结果列表, done => 已执行表名]
     */
    private function tableAction($action = "", $tables = [])
    {
        $action = in_array($action, ["ANALYZE", "OPTIMIZE", "CHECK", "REPAIR"], true)
            ? $action : "";
        $result = [];
        $done   = [];
        if (!$action) return ["list" => $result, "done" => $done];
        $status = $this->tableStatus();
        $status || ajaxout(0, "获取数据表列表失败");
        foreach ($tables as $table) {
            if (!is_array($table)) continue;
            $name = trim((string) ($table['Name'] ?? ""));
            if ($name === "" || !array_key_exists($name, $status)) continue;
            if ($action === "REPAIR" && stripos($status[$name], "InnoDB") !== false) {
                $result[] = [
                    "Table"    => $name,
                    "Msg_type" => "warning",
                    "Msg_text" => "InnoDB 表不支持修复，已跳过",
                ];
                continue;
            }
            $info = sql_query("{$action} TABLE `{$name}`;");
            $rows = [];
            if (is_array($info)) {
                $rows = isset($info[0]) && is_array($info[0]) ? $info : [$info];
            }
            if (!$rows) {
                $result[] = [
                    "Table"    => $name,
                    "Msg_type" => "error",
                    "Msg_text" => "执行失败",
                ];
                continue;
            }
            foreach ($rows as $row) {
                $result[] = [
                    "Table"    => $name,
                    "Msg_type" => $row['Msg_type'] ?? "status",
                    "Msg_text" => $row['Msg_text'] ?? "OK",
                ];
            }
            $done[] = $name;
        }
        return ["list" => $result, "done" => $done];
    }
    /**
     * @description: 获取当前库表名与存储引擎（服务端白名单，不信任前端提交）
     * @return array [表名 => 引擎]
     */
    private function tableStatus()
    {
        $tables = sql_query("SHOW TABLE STATUS");
        if (!is_array($tables)) return [];
        $rows = isset($tables[0]) && is_array($tables[0]) ? $tables : [$tables];
        $data = [];
        foreach ($rows as $row) {
            if (!isset($row['Name'])) continue;
            $data[$row['Name']] = (string) ($row['Engine'] ?? "");
        }
        return $data;
    }
    private function checkRepair($string = "")
    {
        $text = '<span class="need-repair" style="color:red">疑似损坏</span>';
        if (
            $string === "null" ||
            $string === null ||
            $string === ""
        ) {
            return $text;
        }
        return $string;
    }
}
