<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:40:28
 * @LastEditTime: 2023-10-17 18:05:45
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
        $title = $LF['apptitle'] ?: "同步";
        $new   = $this->getNewSql($LF['appname']);
        $diff  = $this->getDiff($new, $this->getKey($new));
        foreach ($diff as $name => $val) {
            $sqls  = [];
            $upkey = [];
            switch ($val['type']) {
                case 'create':
                    foreach ($val['data'] as $key => $data) {
                        if ($this->isKey($key)) {
                            $sqls[] = $this->setKey($key, $data, true);
                        }
                        if ($key == "LCMSDATAINDEX" || $data['index']) {
                            $sqls[] = $this->setIndex($key, $data, true);
                        }
                    }
                    $sqls = array_filter($sqls);
                    if (!$sqls) {
                        continue;
                    }
                    $engine  = $val['data']['LCMSDATAOTHER']['engine'];
                    $engine  = $engine ? " ENGINE={$engine}" : "";
                    $mysql[] = "CREATE TABLE `{$PRE}{$name}` ( " . implode(",\n", $sqls) . "){$engine} DEFAULT CHARSET=utf8mb4;";
                    break;
                default:
                    foreach ($val['data'] as $key => $data) {
                        if ($data['indexdrop']) {
                            $sqls[] = "DROP INDEX `{$key}`";
                            if ($data['continue']) {
                                continue;
                            }
                        }
                        if ($data['columndrop']) {
                            $sqls[] = "DROP COLUMN `{$key}`";
                        } else {
                            if ($this->isKey($key)) {
                                $sqls[]  = $this->setKey($key, $data);
                                $upkey[] = $this->setVal($key, $data);
                            }
                            if ($key == "LCMSDATAINDEX" || $data['index']) {
                                $sqls[] = $this->setIndex($key, $data);
                            }
                            if ($key == "LCMSDATAOTHER") {
                                $sqls[] = $this->setOther($data);
                            }
                        }
                    }
                    foreach ($upkey as $val) {
                        if ($val) {
                            $mysql[] = "UPDATE `{$PRE}{$name}` {$val};";
                        }
                    }
                    $sqls = array_filter($sqls);
                    if (!$sqls) {
                        continue;
                    }
                    $mysql[] = "ALTER TABLE `{$PRE}{$name}` \n" . implode(",\n", $sqls) . ";";
                    break;
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
            ajaxout(1, "数据结构不需要{$title}");
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
            $file = "sys/backup/include/data/main";
        }
        $file = PATH_APP . "{$file}.sql";
        if (is_file($file)) {
            $file = file_get_contents($file);
            if (substr($file, 0, 1) != "{") {
                $file = ssl_decode($file, "SQL");
                $file = gzinflate($file);
            }
            return json_decode($file, true);
        }
        ajaxout(1, "无数据结构文件");
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
                        switch ($key) {
                            case 'LCMSDATAINDEX':
                                foreach ($val as $k => $v) {
                                    $oname = $old[$name][$key][$k]['name'] ?: [];
                                    $diff  = array_diff($v['name'], $oname);
                                    if ($diff) {
                                        if ($old[$name][$key][$k]['name']) {
                                            $val[$k]['indexdrop'] = true;
                                        }
                                    } else {
                                        unset($val[$k]);
                                    }
                                }
                                if ($val) {
                                    $result[$name]['data'][$key] = $val;
                                }
                                break;
                            case 'LCMSDATAOTHER':
                                $diff = array_diff($val, $old[$name][$key]);
                                if ($diff) {
                                    $result[$name]['data'][$key] = $diff;
                                }
                                break;
                            default:
                                $diff = array_diff($val, $old[$name][$key]);
                                if ($diff) {
                                    if (!array_key_exists("default", $diff) && !array_key_exists("index", $diff) && in_string($diff['type'], "int") && !in_string($old[$name][$key]['type'], "int(") && (in_string($diff['type'], "unsigned") == in_string($old[$name][$key]['type'], "unsigned"))) {
                                        continue;
                                    }
                                    if (array_key_exists("index", $diff) && $old[$name][$key]['index'] && $old[$name][$key]['index'] != $diff['index']) {
                                        $diff['indexdrop'] = true;
                                    }
                                    $diff = array_merge([
                                        "type"    => $val['type'],
                                        "index"   => "",
                                        "default" => $val['default'],
                                    ], $diff);
                                    $result[$name]['data'][$key] = $diff;
                                };
                                break;
                        }
                    } else {
                        if ($this->isKey($key)) {
                            $val = array_merge($val, [
                                "add" => true,
                            ]);
                        }
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
        foreach ($old as $name => $data) {
            if ($new[$name]) {
                foreach ($data as $key => $val) {
                    if ($this->isKey($key) && !$new[$name][$key]) {
                        if (substr($key, 0, 4) != "own_") {
                            if ($val['index']) {
                                $result[$name]['data'][$key]['indexdrop'] = true;
                            }
                            $result[$name]['data'][$key]['columndrop'] = true;
                        }
                    }
                }
                foreach ($data['LCMSDATAINDEX'] as $key => $val) {
                    if (!$new[$name]['LCMSDATAINDEX'][$key]) {
                        $result[$name]['data'][$key]['indexdrop'] = true;
                        $result[$name]['data'][$key]['continue']  = true;
                    }
                }
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
        foreach ($_L['table'] as $name) {
            $name = str_replace($PRE, "", $name);
            if ($new[$name]) {
                $tables[] = $name;
            }
        }
        foreach ($tables as $name) {
            $indexs = $this->getIndex($name);
            foreach (sql_query("SHOW FULL COLUMNS FROM {$PRE}{$name}") as $key) {
                if ($indexs[$key['Field']]) {
                    $index = $indexs[$key['Field']]['type'];
                    unset($indexs[$key['Field']]);
                } else {
                    $index = null;
                }
                $result[$name][$key['Field']] = [
                    "type"    => $key['Type'],
                    "index"   => $index,
                    "default" => $key['Extra'] === "auto_increment" ? "AUTO_INCREMENT" : ($key['Default'] != "" ? $key['Default'] : "NULL"),
                ];
            }
            if ($indexs) {
                $result[$name]['LCMSDATAINDEX'] = $indexs;
            }
            $result[$name]['LCMSDATAOTHER'] = [
                "engine" => $this->getEngine($name),
            ];
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
        $indexs = sql_query("SHOW INDEX FROM {$PRE}{$table}");
        $indexs = $indexs['Table'] ? [$indexs] : $indexs;
        foreach ($indexs as $val) {
            $kname = $val['Column_name'];
            $name  = $val['Column_name'];
            if ($val['Key_name'] == "PRIMARY") {
                $type = "PRIMARY";
            } elseif ($val['Non_unique'] == 1) {
                $kname = $val['Key_name'] ?: $val['Column_name'];
                $type  = $val['Index_type'];
            } else {
                $type = "UNIQUE";
            }
            if ($kname) {
                $index[$kname]['type']   = $type;
                $index[$kname]['name'][] = $name;
            }
        }
        return $index ?: [];
    }
    /**
     * @description: 获取引擎
     * @param string $table
     * @return string
     */
    private function getEngine($table)
    {
        global $_L, $LF, $LC;
        $status = sql_query("SHOW TABLE STATUS LIKE '{$_L['mysql']['pre']}{$table}'");
        return $status['Engine'];
    }
    /**
     * @description: 判断是否表字段
     * @param string $key
     * @return bool
     */
    private function isKey($key)
    {
        if (!in_string($key, ["LCMSDATAINDEX", "LCMSDATAOTHER"])) {
            return true;
        }
        return false;
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
        if ($data['index']) {
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
            $sql = $create ? "{$sql} KEY" : "ADD{$sql} INDEX";
            $sql .= $data['index'] != "PRIMARY" ? " `{$key}` " : " ";
            $sql .= "(`{$key}`)";
            $sql .= $end ? " USING BTREE" : "";
        } elseif ($data) {
            foreach ($data as $k => $v) {
                $name  = implode("`, `", $v['name']);
                $sql[] = $create ? " KEY `{$k}` (`{$name}`)" : ($v['indexdrop'] ? "DROP INDEX `{$k}`, " : "") . "ADD INDEX `{$k}` (`{$name}`) USING BTREE";
            }
            $sql = implode(",\n", $sql);
        }
        return $sql;
    }
    private function setOther($data)
    {
        global $_L, $LF, $LC, $PRE;
        $sql = [];
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'engine':
                    $sql[] = "ENGINE = {$v}";
                    break;
            }
        }
        return implode(",\n", $sql);
    }
}
