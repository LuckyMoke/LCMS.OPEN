<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2020-11-16 18:10:00
 * @Description:数据库备份恢复操作
 * @symbol_custom_string_obkoro1_copyright: Copyright ${now_year} 运城市盘石网络科技有限公司
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
                $this->backup_mysql();
                ajaxout(1, "备份成功");
                break;
            case 'restore':
                $this->insertsql($_L['form']['filename']);
                break;
            case 'del':
                $dir = PATH_WEB . "backup/mysql/";
                delfile("{$dir}{$_L['form']['filename']}");
                ajaxout(1, "删除成功");
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
        $dir   = PATH_WEB . "backup/mysql";
        $files = traversal_one($dir);
        foreach ($files['file'] as $file) {
            $result[] = [
                "filename" => $file,
                "size"     => getfilesize("{$dir}/{$file}", "MB"),
                "time"     => datetime(filectime("{$dir}/{$file}")),
                "ver"      => json_decode(file_get_contents("{$dir}/{$file}"), true)['ver'],
            ];
        }
        array_multisort(array_column($result, 'time'), SORT_DESC, $result);
        return $result;
    }
    /**
     * @导入数据库:
     * @param {*}
     * @return {*}
     */
    private function insertsql($filename)
    {
        global $_L;
        $file = PATH_WEB . "backup/mysql/{$filename}";
        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['ver'] == $_L['config']['ver']) {
                $sqldata = str_replace("\r", "", base64_decode($data['mysql']));
                $sqldata = explode(";\n", trim($sqldata));
                foreach ($sqldata as $sql) {
                    if ($sql) {
                        sql_query($sql);
                    }
                }
                ajaxout(1, "恢复成功！");
            } else {
                ajaxout(0, "恢复失败，框架版本不匹配！");
            }
        } else {
            ajaxout(0, "恢复失败，文件不存在！");
        }
    }
    /**
     * @备份数据库操作:
     * @param {*}
     * @return {*}
     */
    private function backup_mysql()
    {
        global $_L;
        $sqldata = [];
        $tables  = sql_query("SHOW TABLE STATUS");
        foreach ($tables as $table) {
            $start = 0;
            $cache = sql_query("SHOW CREATE TABLE {$table['Name']}");
            if ($cache['Create Table']) {
                $sqldata[] = "DROP TABLE IF EXISTS `{$table['Name']}`";
                $sqldata[] = $cache['Create Table'];
            }
            $tablename = str_replace($_L['mysql']['pre'], "", $table['Name']);
            $numrows   = sql_counter([$tablename]);
            while ($start < $numrows) {
                $rows = sql_getall([$tablename, "", "", "", "", "", [$start, 100]]);
                foreach ($rows as $row) {
                    $keys = array_keys($row);
                    $keys = array_map('addslashes', $keys);
                    $keys = implode("`,`", $keys);
                    $keys = "`{$keys}`";
                    $vals = array_values($row);
                    foreach ($vals as $index => $val) {
                        if ($val === null) {
                            $vals[$index] = "[BACKUPNULL]";
                        }
                    }
                    $vals = array_map('addslashes', $vals);
                    $vals = implode("','", $vals);
                    $vals = "'{$vals}'";
                    $vals = str_replace("'[BACKUPNULL]'", "NULL", $vals);

                    $sqldata[] = "INSERT INTO `{$table['Name']}`($keys) values($vals)";
                }
                $start = $start + 100;
            }
        }
        $dir = PATH_WEB . "backup/mysql";
        makedir($dir);
        file_put_contents("{$dir}/" . date("Y-m-d-H-i-s-") . randstr(8) . ".mysql", json_encode_ex([
            "ver"   => $_L['config']['ver'],
            "mysql" => base64_encode(implode(";\n", $sqldata)),
        ]));
    }
}
