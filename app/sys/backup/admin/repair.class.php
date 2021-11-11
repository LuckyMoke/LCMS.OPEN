<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2021-11-10 17:32:08
 * @Description:数据库修复
 * @Copyright 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class repair extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
        LCMS::SUPER() || LCMS::X(403, "仅超级管理员可设置");
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        $title = $LF['apptitle'] ?: "修复";
        $new   = $this->new_sql($LF['appname']);
        $diff  = $this->get_diff($new, $this->get_key($new));
        foreach ($diff as $name => $val) {
            $sql = [];
            if ($val['type'] == "create") {
                foreach ($val['data'] as $key => $data) {
                    $sql[] = $this->sql_key($key, $data, true);
                }
                foreach ($val['data'] as $key => $data) {
                    if ($data['index']) {
                        $sql[] = $this->sql_index($key, $data, true);
                    }
                }
                if (!$sql) {
                    continue;
                }
                $mysql[] = "CREATE TABLE `{$_L['mysql']['pre']}{$name}` ( " . implode(",\n", $sql) . ") ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
            } else {
                foreach ($val['data'] as $key => $data) {
                    $sql[] = $this->sql_key($key, $data);
                }
                foreach ($val['data'] as $key => $data) {
                    if ($data['index']) {
                        $sql[] = $this->sql_index($key, $data);
                    }
                }
                $mysql[] = "ALTER TABLE `{$_L['mysql']['pre']}{$name}` " . implode(",\n", $sql) . ";";
            }
        }
        $mysql = $mysql ? implode("\n\n", $mysql) : [];
        if ($mysql) {
            sql_query($mysql);
            if (sql_error()) {
                ajaxout(0, "{$title}失败：" . sql_error());
            }
            ajaxout(1, "{$title}成功");
        } else {
            ajaxout(0, "数据表不需要{$title}");
        }
    }
    /**
     * @description: 获取数据结构
     * @param string $app
     * @return {*}
     */
    private function new_sql($app = "")
    {
        global $_L, $LF, $LC;
        if ($app) {
            $file = "open/{$app}/app";
        } else {
            $file = "sys/backup/include/data/base";
        }
        $file = PATH_APP . "{$file}.sql";
        if (is_file($file)) {
            $file = file_get_contents($file);
            $file = ssl_decode($file, "SQL");
            $file = gzinflate($file);
            return json_decode($file, true);
        }
        ajaxout(0, "未找到对应数据结构文件");
    }
    /**
     * @description: 对比数据结构不同
     * @param {*} $new
     * @param {*} $old
     * @return {*}
     */
    private function get_diff($new, $old)
    {
        global $_L, $LF, $LC;
        foreach ($new as $name => $data) {
            if ($old[$name]) {
                foreach ($data as $key => $val) {
                    if ($old[$name][$key]) {
                        $diff = array_diff($val, $old[$name][$key]);
                        if ($diff) {
                            $diff = array_merge([
                                "type"    => $val['type'],
                                "index"   => "",
                                "default" => $val['default'],
                            ], $diff);
                            $result[$name]['data'][$key] = $diff;
                        };
                    } else {
                        $val['update']               = true;
                        $result[$name]['data'][$key] = $val;
                    }
                }
                if ($result[$name]['data']) {
                    $result[$name]['type'] = "update";
                }
            } else {
                $result[$name] = [
                    "type" => "create",
                    "data" => $data,
                ];
            }
        }
        return $result ?: [];
    }
    /**
     * @description: 获取数据库中表键
     * @param {*} $table
     * @return {*}
     */
    private function get_key($new)
    {
        global $_L, $LF, $LC;
        foreach (DB::$mysql->get_tables() as $name) {
            $name = str_replace($_L['mysql']['pre'], "", $name);
            if ($new[$name]) {
                $tables[] = $name;
            }
        }
        foreach ($tables as $name) {
            $indexs = $this->get_index($name);
            foreach (sql_query("SHOW FULL COLUMNS FROM {$_L['mysql']['pre']}{$name}") as $key) {
                $result[$name][$key['Field']] = [
                    "type"    => $key['Type'],
                    "index"   => $indexs[$key['Field']],
                    "default" => $key['Extra'] == "auto_increment" ? "AUTO_INCREMENT" : ($key['Default'] != "" ? $key['Default'] : "NULL"),
                ];
            }
        }
        return $result ?: [];
    }
    /**
     * @description: 获取数据库中表索引
     * @param {*} $table
     * @return {*}
     */
    private function get_index($table)
    {
        global $_L, $LF, $LC;
        foreach (sql_query("SHOW INDEX FROM {$_L['mysql']['pre']}{$table}") as $val) {
            if (isset($val['Key_name']) && $val['Key_name'] == "PRIMARY") {
                $key = "PRIMARY";
            } elseif (isset($val['Non_unique']) && $val['Non_unique'] == "1") {
                $key = "NORMAL";
            } else {
                $key = "UNIQUE";
            }
            $index[isset($val['Column_name']) ? $val['Column_name'] : ""] = $key;
        }
        return $index ?: [];
    }
    /**
     * @description: 创建字段语句
     * @param {*} $key
     * @param {*} $data
     * @param {*} $create
     * @return {*}
     */
    private function sql_key($key, $data, $create = false)
    {
        global $_L, $LF, $LC;
        $sql = $create ? "`{$key}` {$data['type']}" : ($data['update'] ? "ADD" : "MODIFY") . " COLUMN `{$key}` {$data['type']}";
        if ($data['default'] === "AUTO_INCREMENT") {
            $sql .= " NOT NULL AUTO_INCREMENT";
        } elseif ($data['default'] === "NULL") {
            $sql .= " NULL";
        } elseif ($data['default'] !== "") {
            $sql .= " NOT NULL DEFAULT {$data['default']}";
        }
        return $sql;
    }
    /**
     * @description: 创建索引语句
     * @param {*} $key
     * @param {*} $data
     * @param {*} $create
     * @param {*} $del
     * @return {*}
     */
    private function sql_index($key, $data, $create = false)
    {
        global $_L, $LF, $LC;
        switch ($data['index']) {
            case 'PRIMARY':
                $sql = " PRIMARY";
                break;
            case 'UNIQUE':
                $sql = " UNIQUE";
                break;
        }
        $sql = $create ? "{$sql} KEY" : "ADD{$sql} INDEX";
        $sql .= $data['index'] != "PRIMARY" ? " `{$key}`" : " ";
        $sql .= "(`{$key}`)";
        $sql .= " USING BTREE";
        return $sql;
    }
}
