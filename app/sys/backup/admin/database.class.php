<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2021-03-04 14:25:10
 * @Description:数据库备份恢复操作
 * @Copyright 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class database extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'backup':
                $table                     = sql_query("SHOW TABLE STATUS");
                is_array($table) && $table = array_column($table, "Name");
                makedir(PATH_WEB . "backup/data/");
                delfile(PATH_WEB . "backup/backup.sql");
                ajaxout(1, "success", "", $table);
                break;
            case 'backup-table':
                set_time_limit(300);
                $this->export_table($_L['form']['name']);
                ajaxout(1, "success");
                break;
            case 'backup-ok':
                set_time_limit(300);
                $this->export_mysql();
                break;
            case 'restore':
                ini_set("memory_limit", -1);
                set_time_limit(300);
                $this->insertsql();
                break;
            case 'del':
                $file = PATH_WEB . "backup/data/{$_L['form']['name']}";
                if (is_file($file)) {
                    delfile($file);
                    ajaxout(1, "删除成功");
                } else {
                    ajaxout(0, "文件不存在");
                }
                break;
            default:
                $mysql = $this->get_mysql();
                require LCMS::template("own/database/index");
                break;
        }
    }
    /**
     * @获取数据库备份文件信息:
     * @param {*}
     * @return {*}
     */
    private function get_mysql()
    {
        global $_L;
        $dir   = PATH_WEB . "backup/data/";
        $files = traversal_one($dir, "LCMS");
        foreach ($files['file'] as $file) {
            if (is_file("{$dir}{$file}")) {
                $info     = str_replace(["DATA#V", ".LCMS", "&"], ["", "", " "], $file);
                $info     = explode("#", $info);
                $result[] = [
                    "name" => $file,
                    "ver"  => $info[0],
                    "time" => str_replace(["T", "."], ["", ":"], $info[1]),
                    "size" => getfilesize("{$dir}{$file}"),
                ];
            }
        }
        !empty($result) && array_multisort(array_column($result, 'time'), SORT_DESC, $result);
        return $result;
    }
    /**
     * @导入数据库:
     * @param {*}
     * @return {*}
     */
    private function insertsql()
    {
        global $_L;
        $path  = PATH_WEB . "backup/";
        $file  = "{$path}data/{$_L['form']['name']}";
        $cache = "{$path}backup.sql";
        if (is_file($file)) {
            if ($_L['form']['ver'] == $_L['config']['ver']) {
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
                    ajaxout(1, "恢复成功！");
                } else {
                    ajaxout(0, "恢复失败，文件不存在！");
                }
            } else {
                ajaxout(0, "恢复失败，框架版本不匹配！");
            }
        } else {
            ajaxout(0, "恢复失败，文件不存在！");
        }
    }
    /**
     * @导出数据表:
     * @param {*}
     * @return {*}
     */
    private function export_table($name)
    {
        global $_L;
        $start  = 0;
        $cache  = PATH_WEB . "backup/backup.sql";
        $create = sql_query("SHOW CREATE TABLE {$name}");
        if ($create['Create Table']) {
            file_put_contents($cache, "DROP TABLE IF EXISTS `{$name}`;\n\n{$create['Create Table']};\n\n", FILE_APPEND);
        }
        $tablename = str_replace($_L['mysql']['pre'], "", $name);
        $numrows   = sql_counter([$tablename]);
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
                    $tmp = str_replace(["\r\n", "\n"], "\\r\\n", $tmp);
                    $vals .= "('" . implode("','", $tmp) . "'),";
                }
                $vals = rtrim($vals, ",");
                $vals = str_replace("'[BACKUPNULL]'", "NULL", $vals);
                file_put_contents($cache, "INSERT INTO `{$name}` VALUES {$vals};\n\n", FILE_APPEND);
            }
            $start = $start + 500;
        }
    }
    /**
     * @备份数据库操作:
     * @param {*}
     * @return {*}
     */
    private function export_mysql()
    {
        global $_L;
        $path  = PATH_WEB . "backup/";
        $cache = "{$path}backup.sql";
        if (is_file($cache)) {
            $bpath = "{$path}data/";
            $bname = "DATA#V{$_L['config']['ver']}#T" . date("Y-m-d&H.i.s") . "#" . randstr(6);
            if (zipfile([
                [$cache, "backup.sql"],
            ], "{$bpath}{$bname}.LCMS")) {
                delfile($cache);
                ajaxout(1, "备份成功");
            } else {
                ajaxout(0, "备份失败");
            }
        } else {
            ajaxout(0, "备份失败");
        }
    }
}
