<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2024-12-18 20:28:36
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
        global $_L, $LF, $LC, $PATH;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
        $PATH = PATH_WEB . "backup/";
    }
    public function doindex()
    {
        global $_L, $LF, $LC, $PATH;
        switch ($LF['action']) {
            case 'list':
                $bklist = $this->getBackList();
                $data   = array_slice($bklist, ($LF['page'] - 1) * $LF['limit'], $LF['limit']);
                foreach ($data as $index => $val) {
                    $data[$index] = array_merge($val, [
                        "link" => '<text class="lcms-table-td-icon"><img src="/public/static/images/icons/zip.svg"/></text> ' . $val['name'],
                    ]);
                }
                TABLE::$count = count($bklist);
                TABLE::out($data);
                break;
            case 'backup':
                makedir("{$PATH}data/");
                delfile("{$PATH}backup.sql");
                $tables = array_keys($_L['table']);
                require LCMS::template("own/database/backup");
                break;
            case 'backup-ok':
                ini_set("memory_limit", -1);
                ignore_user_abort(true);
                set_time_limit(300);
                $cache = "{$PATH}backup.sql";
                if (is_file($cache)) {
                    $bpath   = "{$PATH}data/";
                    $version = file_get_contents(PATH_CORE . "version");
                    $bname   = "DATA#V{$version}#T" . date("Y-m-d&H.i.s") . "#" . randstr(6);
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
                ajaxout(0, "压缩失败");
                break;
            case 'restore':
                ini_set("memory_limit", -1);
                ignore_user_abort(true);
                set_time_limit(300);
                $file  = "{$PATH}data/{$LC['name']}";
                $cache = "{$PATH}backup.sql";
                if (is_file($file)) {
                    $version = file_get_contents(PATH_CORE . "version");
                    if ($LC['ver'] == $version) {
                        unzipfile($file, $PATH);
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
                        ["title" => "立即备份", "event" => "openExport"],
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
    public function doexport()
    {
        global $_L, $LF, $LC, $PATH;
        $cache = "{$PATH}backup.sql";
        $table = $LF['table'];
        if ($LF['page'] == 1) {
            $create = sql_query("SHOW CREATE TABLE {$_L['table'][$table]}");
            if ($create['Create Table']) {
                file_put_contents($cache, "DROP TABLE IF EXISTS `{$_L['table'][$table]}`;\n\n{$create['Create Table']};\n\n", FILE_APPEND);
            }
        }
        switch ($table) {
            case 'cache':
                $total = 1;
                break;
            default:
                $count   = 1000;
                $counted = $LF['page'] * $count;
                $total   = sql_counter([$table]);
                if ($total > 0) {
                    if ($counted < $total) {
                        $next = ($LF['page'] * 1) + 1;
                    }
                    $total = ceil($total / $count);
                    $rows  = sql_getall([$table, null, null, null, null, null, [$counted - $count, $count]]);
                    $vals  = [];
                    foreach ($rows as $index => $row) {
                        $tmp = [];
                        foreach ($row as $key => $val) {
                            if ($val === null) {
                                $tmp[] = "[LCMSBACKUPNULL]";
                            } else {
                                $tmp[] = $val;
                            }
                        }
                        $tmp    = array_map('addslashes', $tmp);
                        $tmp    = str_replace(["\r\n", "\n"], "\\n", $tmp);
                        $tmp    = implode("', '", $tmp);
                        $vals[] = "('{$tmp}')";
                    }
                    $keys = implode("`, `", array_keys($rows[0]));
                    $vals = implode(", ", $vals);
                    $sql  = "INSERT INTO {$_L['table'][$table]} (`{$keys}`) VALUES {$vals};\n\n";
                    $sql  = str_replace("'[LCMSBACKUPNULL]'", "NULL", $sql);
                    file_put_contents($cache, $sql, FILE_APPEND);
                }
                break;
        }
        ajaxout(1, "success", "", [
            "total" => $total ?: 1,
            "next"  => $next ?? 0,
        ]);
    }
    /**
     * @获取数据库备份文件信息:
     * @param {*}
     * @return {*}
     */
    private function getBackList()
    {
        global $_L, $LF, $LC, $PATH;
        $bkpath = "{$PATH}data/";
        $bklist = LCMS::cache("lcms_backuplist", [], true);
        $dtime  = filemtime($bkpath);
        if ($bklist && $bklist['time'] == $dtime) {
            $bklist = $bklist['list'];
        } else {
            $bklist = traversal_one($bkpath, "\.LCMS");
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
}
