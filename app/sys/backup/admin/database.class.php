<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2023-03-04 15:54:10
 * @Description: 数据库备份恢复操作
 * @Copyright 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class database extends adminbase
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
                $bklist = $this->getBackList();
                $data   = array_slice($bklist, ($LF['page'] - 1) * $LF['limit'], $LF['limit']);
                foreach ($data as $index => $val) {
                    $data[$index] = array_merge($val, [
                        "link" => "<a href='{$_L['url']['site']}backup/data/" . urlencode($val['name']) . "' target='_blank'><i class='layui-icon layui-icon-unlink'></i> {$val['name']}</a>",
                    ]);
                }
                TABLE::$count = count($bklist);
                TABLE::out($data);
                break;
            case 'backup':
                makedir("/backup/data/");
                delfile("/backup/backup.sql");
                ajaxout(2, [
                    "title" => "开始备份",
                    "msg"   => "正在获取数据表信息",
                ], "backupDatabase", array_values($_L['table']));
                break;
            case 'backup-table':
                set_time_limit(300);
                $this->exportTable($LF['name']);
                ajaxout(1, "success");
                break;
            case 'backup-ok':
                set_time_limit(300);
                $this->exportMysql();
                break;
            case 'restore':
                ini_set("memory_limit", -1);
                set_time_limit(300);
                $path  = PATH_WEB . "backup/";
                $file  = "{$path}data/{$LC['name']}";
                $cache = "{$path}backup.sql";
                if (is_file($file)) {
                    if ($LC['ver'] == $_L['config']['ver']) {
                        unzipfile($file, $path);
                        if (is_file($cache)) {
                            $sqldata = file_get_contents($cache);
                            $sqldata = explode(";\n\n", trim($sqldata));
                            foreach ($sqldata as $sql) {
                                if ($sql) {
                                    sql_query($sql);
                                }
                            }
                            delfile($cache);
                            LCMS::log([
                                "type" => "system",
                                "info" => "数据恢复-恢复成功-{$LC['name']}",
                            ]);
                            ajaxout(1, "恢复成功");
                        }
                    } else {
                        ajaxout(0, [
                            "title" => "恢复失败",
                            "msg"   => "框架版本不匹配",
                        ]);
                    }
                }
                ajaxout(0, "恢复失败");
                break;
            case 'del':
                if (delfile("/backup/data/{$LC['name']}")) {
                    LCMS::log([
                        "type" => "system",
                        "info" => "数据备份-删除备份-{$LC['name']}",
                    ]);
                    ajaxout(1, "删除成功", "reload");
                } else {
                    ajaxout(0, "文件不存在");
                }
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["title"   => "备份名称", "field" => "link",
                            "minWidth" => 200],
                        ["title" => "数据大小", "field" => "size",
                            "width"  => 150,
                            "align"  => "center"],
                        ["title" => "备份时间", "field" => "time",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title" => "框架版本", "field" => "ver",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 95,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title"  => "恢复", "event" => "ajax",
                                    "url"     => "index&action=restore",
                                    "color"   => "default",
                                    "timeout" => 0,
                                    "tips"    => "确认恢复此备份？"],
                                ["title" => "删除", "event" => "ajax",
                                    "url"    => "index&action=del",
                                    "color"  => "danger",
                                    "tips"   => "确认删除此备份？"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => "立即备份", "event" => "ajax",
                            "url"    => "index&action=backup",
                            "color"  => "default",
                            "tips"   => "确认备份数据库？"],
                        ["title" => "同步数据结构", "event" => "ajax",
                            "url"    => "&c=repair",
                            "color"  => "warm",
                            "tips"   => "手动更新框架可能会出现数据库结构与最新版不匹配的情况，使用此功能同步数据结构！<br/><span style=\"color:red\">请先做好数据库备份！！！</span>"],
                    ],
                ];
                require LCMS::template("own/database/index");
                break;
        }
    }
    /**
     * @获取数据库备份文件信息:
     * @param {*}
     * @return {*}
     */
    private function getBackList()
    {
        global $_L, $LF, $LC;
        $bkpath = PATH_WEB . "backup/data";
        $bklist = LCMS::cache("lcms_backuplist", [], true);
        $dtime  = filemtime($bkpath);
        if ($bklist && $bklist['time'] == $dtime) {
            $bklist = $bklist['list'];
        } else {
            $bklist = traversal_one($bkpath, "LCMS");
            $bklist = $bklist['file'] ?: [];
            foreach ($bklist as $index => $li) {
                $info = str_replace([
                    "DATA#V", ".LCMS", "&",
                ], ["", "", " "], $li);
                $info           = explode("#", $info);
                $bklist[$index] = [
                    "name" => $li,
                    "ver"  => $info[0],
                    "time" => str_replace([
                        "T", "."], [
                        "", ":"], $info[1]),
                    "size" => getfilesize("{$bkpath}/{$li}"),
                ];
            }
            !empty($bklist) && array_multisort(array_column($bklist, 'time'), SORT_DESC, $bklist);
            LCMS::cache("lcms_backuplist", [
                "time" => $dtime,
                "list" => $bklist,
            ], true);
        }
        return $bklist ?: [];
    }
    /**
     * @description: 导出数据表
     * @param string $name
     * @return {*}
     */
    private function exportTable($name)
    {
        global $_L, $LF, $LC;
        $start  = 0;
        $cache  = PATH_WEB . "backup/backup.sql";
        $create = sql_query("SHOW CREATE TABLE {$name}");
        if ($create['Create Table']) {
            file_put_contents($cache, "DROP TABLE IF EXISTS `{$name}`;\n\n{$create['Create Table']};\n\n", FILE_APPEND);
        }
        $tablename = str_replace($_L['mysql']['pre'], "", $name);
        $numrows   = sql_counter([$tablename]);
        if ($tablename != "cache" && $tablename != "log") {
            while ($start < $numrows) {
                $rows = sql_getall([$tablename, "", "", "", "", "", [$start, 500]]);
                if (!empty($rows)) {
                    $vals = "";
                    foreach ($rows as $row) {
                        $tmp = [];
                        foreach ($row as $k => $v) {
                            $tmp[] = $v === null ? "[BACKUPNULL]" : $v;
                        }
                        $tmp = array_map('addslashes', $tmp);
                        $tmp = str_replace(["\r\n", "\n"], "\\n", $tmp);
                        $vals .= "('" . implode("','", $tmp) . "'),";
                    }
                    $vals = rtrim($vals, ",");
                    $vals = str_replace("'[BACKUPNULL]'", "NULL", $vals);
                    file_put_contents($cache, "INSERT INTO `{$name}` VALUES {$vals};\n\n", FILE_APPEND);
                }
                $start = $start + 500;
            }
        }
    }
    /**
     * @description: 备份数据库
     * @param {*}
     * @return {*}
     */
    private function exportMysql()
    {
        global $_L, $LF, $LC;
        $path  = PATH_WEB . "backup/";
        $cache = "{$path}backup.sql";
        if (is_file($cache)) {
            $bpath = "{$path}data/";
            $bname = "DATA#V{$_L['config']['ver']}#T" . date("Y-m-d&H.i.s") . "#" . randstr(6);
            if (zipfile([
                [$cache, "backup.sql"],
            ], "{$bpath}{$bname}.LCMS")) {
                delfile($cache);
                LCMS::log([
                    "type" => "system",
                    "info" => "数据备份-备份成功-{$bname}.LCMS",
                ]);
                ajaxout(1, "备份成功");
            }
        }
        LCMS::log([
            "type" => "system",
            "info" => "数据备份-备份失败",
        ]);
        ajaxout(0, "备份失败");
    }
}
