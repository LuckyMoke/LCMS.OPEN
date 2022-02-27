<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2022-02-27 14:31:00
 * @Description:数据库修复
 * @Copyright 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class repair extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC, $PRE;
        parent::__construct();
        $LF  = $_L['form'];
        $LC  = $LF['LC'];
        $PRE = $_L['mysql']['pre'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC, $PRE;
        $title = $LF['apptitle'] ?: "修复";
        $new   = $this->getNewSql($LF['appname']);
        $diff  = $this->getDiff($new, $this->getKey($new));
        foreach ($diff as $name => $val) {
            $sqls  = [];
            $upkey = [];
            if ($val['type'] == "create") {
                foreach ($val['data'] as $key => $data) {
                    $sqls[] = $this->setKey($key, $data, true);
                }
                foreach ($val['data'] as $key => $data) {
                    if ($data['index']) {
                        $sqls[] = $this->setIndex($key, $data, true);
                    }
                }
                if (!$sqls) {
                    continue;
                }
                $mysql[] = "CREATE TABLE `{$PRE}{$name}` ( " . implode(",\n", $sqls) . ") ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
            } else {
                foreach ($val['data'] as $key => $data) {
                    $sqls[]  = $this->setKey($key, $data);
                    $upkey[] = $this->setVal($key, $data);
                }
                foreach ($val['data'] as $key => $data) {
                    if ($data['index']) {
                        $sqls[] = $this->setIndex($key, $data);
                    }
                }
                foreach ($upkey as $val) {
                    if ($val) {
                        $mysql[] = "UPDATE `{$PRE}{$name}` {$val};";
                    }
                }
                $mysql[] = "ALTER TABLE `{$PRE}{$name}` " . implode(",\n", $sqls) . ";";
            }
        }
        $mysql = $mysql ? implode("\n\n", $mysql) : [];
        if ($mysql) {
            sql_query($mysql);
            if (sql_error()) {
                LCMS::log([
                    "type" => "system",
                    "info" => "数据{$title}-{$title}失败",
                ]);
                ajaxout(0, "{$title}失败：" . sql_error());
            }
            LCMS::log([
                "type" => "system",
                "info" => "数据{$title}-{$title}成功",
            ]);
            ajaxout(1, "{$title}成功");
        } else {
            ajaxout(0, "数据表不需要{$title}");
        }
    }
    /**
     * @description: 获取数据结构
     * @param string $app
     * @return array
     */
    private function getNewSql($app = "")
    {
        global $_L, $LF, $LC, $PRE;
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
     * @param array $new
     * @param array $old
     * @return array
     */
    private function getDiff($new, $old)
    {
        global $_L, $LF, $LC, $PRE;
        foreach ($new as $name => $data) {
            if ($old[$name]) {
                foreach ($data as $key => $val) {
                    if ($old[$name][$key]) {
                        $diff = array_diff($val, $old[$name][$key]);
                        if ($diff) {
                            if ($old[$name][$key]['index'] && $old[$name][$key]['index'] != $diff['index']) {
                                $diff['indexdrop'] = true;
                            }
                            $diff = array_merge([
                                "type"    => $val['type'],
                                "index"   => "",
                                "default" => $val['default'],
                            ], $diff);
                            $result[$name]['data'][$key] = $diff;
                        };
                    } else {
                        $result[$name]['data'][$key] = array_merge($val, [
                            "add" => true,
                        ]);
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
     * @param array $table
     * @return array
     */
    private function getKey($new)
    {
        global $_L, $LF, $LC, $PRE;
        foreach (DB::$mysql->get_tables() as $name) {
            $name = str_replace($PRE, "", $name);
            if ($new[$name]) {
                $tables[] = $name;
            }
        }
        foreach ($tables as $name) {
            $indexs = $this->getIndex($name);
            foreach (sql_query("SHOW FULL COLUMNS FROM {$PRE}{$name}") as $key) {
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
     * @param string $table
     * @return array
     */
    private function getIndex($table)
    {
        global $_L, $LF, $LC, $PRE;
        foreach (sql_query("SHOW INDEX FROM {$PRE}{$table}") as $val) {
            if (isset($val['Key_name']) && $val['Key_name'] == "PRIMARY") {
                $key = "PRIMARY";
            } elseif (isset($val['Non_unique']) && $val['Non_unique'] == "1") {
                $key = $val['Index_type'];
            } else {
                $key = "UNIQUE";
            }
            $index[isset($val['Column_name']) ? $val['Column_name'] : ""] = $key;
        }
        return $index ?: [];
    }
    /**
     * @description: 创建字段语句
     * @param string $key
     * @param array $data
     * @param bool $create
     * @return string
     */
    private function setKey($key, $data, $create = false)
    {
        global $_L, $LF, $LC, $PRE;
        $sql = $create ? "`{$key}` {$data['type']}" : ($data['add'] ? "ADD" : "MODIFY") . " COLUMN `{$key}` {$data['type']}";
        if ($data['default'] === "AUTO_INCREMENT") {
            $sql .= " NOT NULL AUTO_INCREMENT";
        } elseif ($data['default'] === "NULL") {
            $sql .= " NULL";
        } elseif ($data['default'] !== "") {
            $sql .= " NOT NULL DEFAULT";
            if (is_numeric($data['default'])) {
                $sql .= " {$data['default']}";
            } else {
                $sql .= " '{$data['default']}'";
            }
        }
        return $sql;
    }
    /**
     * @description: 更新字段已有数据语句
     * @param string $key
     * @param array $data
     * @return string
     */
    private function setVal($key, $data)
    {
        global $_L, $LF, $LC, $PRE;
        if (!$data['add'] && $data['default'] !== "" && $data['default'] !== "NULL") {
            if (is_numeric($data['default'])) {
                $val = $data['default'];
            } else {
                $val = "'{$data['default']}'";
            }
            $sql = "SET {$key} = {$val} WHERE {$key} IS NULL";
        }
        return $sql ?: "";
    }
    /**
     * @description: 创建索引语句
     * @param string $key
     * @param array $data
     * @param bool $create
     * @return string
     */
    private function setIndex($key, $data, $create = false)
    {
        global $_L, $LF, $LC, $PRE;
        switch ($data['index']) {
            case 'PRIMARY':
                $sql = " PRIMARY";
                $end = true;
                break;
            case 'UNIQUE':
                $sql = " UNIQUE";
                $end = true;
                break;
            case 'BTREE':
                $end = true;
                break;
            case 'FULLTEXT':
                $sql = " FULLTEXT";
                $end = false;
                break;
            case 'SPATIAL':
                $sql = " SPATIAL";
                $end = false;
                break;
        }
        $sql = $create ? "{$sql} KEY" : ($data['indexdrop'] ? "DROP INDEX `{$key}`, " : "") . "ADD{$sql} INDEX";
        $sql .= $data['index'] != "PRIMARY" ? " `{$key}`" : " ";
        $sql .= "(`{$key}`)";
        $sql .= $end ? " USING BTREE" : "";
        return $sql;
    }
}
