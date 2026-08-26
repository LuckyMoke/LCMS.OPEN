<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-03-07 15:50:06
 * @LastEditTime: 2026-08-25 16:34:22
 * @Description: Mysql数据库操作类
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class MYSQL
{
    private $config;
    private $master;
    private $slave;
    private $lastdb;
    private $_table;
    private $_where;
    private $_order;
    private $_bind;
    private $_fields;
    private $_limit;
    private $_setkey;
    private $_data;
    private $_math;
    private $_sql;
    private $_force_master = false;
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->master = new SQLPDO("mysql:host={$config['host']};dbname={$config['name']};port={$config['port']};charset={$config['charset']}", $config['user'], $config['pass']);
        $this->lastdb = $this->master;
        if ($config['slave']['host']) {
            $this->slave = new SQLPDO("mysql:host={$config['slave']['host']};dbname={$config['slave']['name']};port={$config['slave']['port']};charset={$config['charset']}", $config['slave']['user'], $config['slave']['pass']);
        }
    }
    /**
     * @description: 自动路由目标数据库
     * @param string $sql
     * @return SQLPDO
     */
    private function getDb($sql = "")
    {
        if ($this->_force_master) {
            return $this->master;
        }
        //通过SQL首词判断是读还是写
        $isread = preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $sql);
        return ($isread && $this->slave) ? $this->slave : $this->master;
    }
    /**
     * @description: 强制使用主库
     * @param bool $flag
     * @return self
     */
    public function forceMaster($flag = true)
    {
        $this->_force_master = $flag;
        return $this;
    }
    /**
     * @description: 指定表
     * @param string $table
     * @return {*}
     */
    public function table($table)
    {
        $this->_table  = $this->config['pre'] . $table;
        $this->_where  = "";
        $this->_order  = "";
        $this->_bind   = [];
        $this->_fields = "*";
        $this->_limit  = "";
        $this->_setkey = "";
        $this->_data   = [];
        $this->_math   = [];
        $this->_sql    = "";
        return $this;
    }
    /**
     * @description: 查询条件
     * @param string $where
     * @return {*}
     */
    public function where($where = "")
    {
        $this->filter($where);
        $this->_where = $where ? " WHERE {$where}" : "";
        return $this;
    }
    /**
     * @description: 排序
     * @param string $order
     * @return {*}
     */
    public function order($order = "")
    {
        $this->_order = $order ? " ORDER BY {$order}" : "";
        return $this;
    }
    /**
     * @description: 绑定数据
     * @param array $bind
     * @return {*}
     */
    public function bind($bind = [], $clear = true)
    {
        $this->bindFilter($bind, $clear);
        return $this;
    }
    /**
     * @description: 查询fields
     * @param string|array $fields
     * @return {*}
     */
    public function fields($fields = "")
    {
        $this->_fields = is_array($fields) ? implode(", ", $fields) : ($fields ?: "*");
        return $this;
    }
    /**
     * @description: 数据
     * @param string|array $data
     * @return {*}
     */
    public function data($data = [])
    {
        if (is_array($data)) {
            $datas = (isset($data[0]) && is_array($data[0])) ? $data : [$data];
            $keys  = array_keys($datas[0]);
            $vals  = [];
            $i     = 0;
            foreach ($datas as $row) {
                $rvals = [];
                foreach ($keys as $key) {
                    $val = $row[$key];
                    if (is_array($val)) {
                        $type = strtoupper($val[0]);
                        if ($type === "EXP") {
                            $rvals[] = $val[1];
                        } elseif (in_array($type, [
                            "+", "-", "*", "/", "%",
                        ])) {
                            $ph      = ":_val_{$i}";
                            $rvals[] = "`{$key}` {$type} {$ph}";
                            //绑定参数
                            $this->_bind[$ph] = $val[1];
                            $i++;
                        } else {
                            //获取参数
                            $args = array_slice($val, 1);
                            //生成
                            $rvals[] = "{$type}(" . implode(", ", $args) . ")";
                            foreach ($args as $nval) {
                                if (
                                    is_string($nval) &&
                                    substr($nval, 0, 1) == ":"
                                ) {
                                    $this->_bind[$nval] = "@LCMS_BIND";
                                }
                            }
                        }
                    } else {
                        if (
                            is_string($val) &&
                            substr($val, 0, 1) == ":"
                        ) {
                            $rvals[] = $val;
                        } else {
                            $ph      = ":_val_{$i}";
                            $rvals[] = $ph;
                            //绑定参数
                            $this->_bind[$ph] = $val;
                            $i++;
                        }
                    }
                }
                $vals[] = $rvals;
            }
            $this->_data = [
                "keys" => $keys,
                "vals" => $vals,
            ];
        } else {
            $this->_data = $data;
        }
        return $this;
    }
    /**
     * @description: 计数更新
     * @param array $math
     * @return {*}
     */
    public function math($math = [])
    {
        $allowed = ["+", "-", "*", "/", "%"];
        foreach ($math as $key => $op) {
            $op = trim($op);
            if (in_array($op, $allowed)) {
                $this->_math[$key] = $op;
            }
        }
        return $this;
    }
    /**
     * @description: 查询条数
     * @param string|array $limit
     * @return {*}
     */
    public function limit($limit = "")
    {
        if (
            $limit === "" ||
            $limit === null ||
            $limit === false
        ) {
            $this->_limit = "";
            return $this;
        }
        if (!is_array($limit)) {
            $limit = explode(",", (string) $limit);
        }
        $larr = array_map(function ($val) {
            return intval(trim($val));
        }, $limit);
        $limit = isset($larr[0]) ? $larr[0] : 0;
        if (isset($larr[1]) && $larr[1] > 0) {
            $limit .= ",{$larr[1]}";
        }
        $this->_limit = $limit ? " LIMIT {$limit}" : "";
        return $this;
    }
    /**
     * @description: 设置数组Key
     * @param string $setkey
     * @return {*}
     */
    public function setkey($setkey = "")
    {
        $this->_setkey = $setkey;
        return $this;
    }
    /**
     * @description: 自定义语句
     * @param string $sql
     * @return {*}
     */
    public function query($sql = "", $bind = [])
    {
        $this->_sql = $sql;
        self::bind($bind, false);
        $this->lastdb = $this->getDb($sql);
        return $this->lastdb->query($this->_sql, $this->_bind);
    }
    /**
     * @description: 查询操作
     * @param bool $one
     * @return array
     */
    public function select($one = false)
    {
        $query        = "SELECT {$this->_fields} FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}";
        $this->lastdb = $this->getDb($query);

        if ($one) {
            $result = $this->lastdb->get_one($query, $this->_bind);
        } else {
            $result = $this->lastdb->get_all($query, $this->_bind);
            if ($this->_setkey && $result) {
                $result = array_column($result, null, $this->_setkey);
            }
        }
        return $result ?: [];
    }
    /**
     * @description: 更新操作
     * @return {*}
     */
    public function update()
    {
        if (empty($this->_data['keys'])) return false;
        $data = [];
        foreach ($this->_data['keys'] as $index => $key) {
            $val = $this->_data['vals'][0][$index];
            if (isset($this->_math[$key])) {
                $val = "`{$key}` {$this->_math[$key]} {$val}";
            }
            $data[] = "`{$key}` = {$val}";
        }
        $data         = $data ? implode(", ", $data) : "";
        $query        = "UPDATE {$this->_table} SET {$data}{$this->_where}";
        $this->lastdb = $this->getDb($query);
        return $this->lastdb->update($query, $this->_bind);
    }
    /**
     * @description: 插入操作
     * @return {*}
     */
    public function insert()
    {
        $keysArr = array_map(function ($key) {
            return "`{$key}`";
        }, $this->_data['keys']);
        $valsArr = array_map(function ($val) {
            return "(" . implode(", ", $val) . ")";
        }, $this->_data['vals']);
        $keys         = "(" . implode(", ", $keysArr) . ")";
        $vals         = implode(", ", $valsArr);
        $query        = "INSERT IGNORE INTO {$this->_table} {$keys} VALUES {$vals}";
        $this->lastdb = $this->getDb($query);
        $this->lastdb->insert($query, $this->_bind);
        return $this->insert_id();
    }
    /**
     * @description: 删除操作
     * @return {*}
     */
    public function delete()
    {
        $query        = "DELETE FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}";
        $this->lastdb = $this->getDb($query);
        return $this->lastdb->delete($query, $this->_bind);
    }
    /**
     * @description: 统计条数
     * @return int
     */
    public function count()
    {
        $fields       = $this->_fields !== "*" ? "COUNT({$this->_fields})" : "COUNT(*)";
        $query        = "SELECT {$fields} FROM {$this->_table}{$this->_where}";
        $this->lastdb = $this->getDb($query);
        $count        = $this->lastdb->counter($query, $this->_bind);
        return $count ? intval($count) : 0;
    }
    /**
     * @description: 统计计算操作
     * @return array
     */
    public function total()
    {
        $fields = str_replace([
            "SUM(", "COUNT(", "AVG(", "MAX(", "MIN(",
        ], [
            "SUM(a.", "COUNT(a.", "AVG(a.", "MAX(a.", "MIN(a.",
        ], $this->_fields);
        $query        = "SELECT {$fields} FROM (SELECT * FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}) AS a";
        $this->lastdb = $this->getDb($query);
        $result       = $this->lastdb->get_all($query, $this->_bind);
        return $result ? $result[0] : [];
    }
    /**
     * @description: 事务开始
     * @return {*}
     */
    public function begin()
    {
        $this->_force_master = true;
        $this->lastdb        = $this->master;
        $this->master->query("BEGIN");
    }
    /**
     * @description: 事务回滚
     * @return {*}
     */
    public function rollback()
    {
        $this->master->query("ROLLBACK");
        $this->_force_master = false;
    }
    /**
     * @description: 事务提交
     * @return bool
     */
    public function commit()
    {
        if ($this->master->error()) {
            $this->master->query("ROLLBACK");
            $this->_force_master = false;
            return false;
        } else {
            $this->master->query("COMMIT");
            $this->_force_master = false;
            return true;
        }
    }
    /**
     * @description: 获取所有数据表名
     * @return array
     */
    public function get_tables()
    {
        $this->lastdb = $this->slave ?: $this->master;
        return $this->lastdb->get_tables();
    }
    /**
     * @description: 获取上次插入数据的ID
     * @return int
     */
    public function insert_id()
    {
        return $this->lastdb ? $this->lastdb->insert_id() : 0;
    }
    /**
     * @description: 获取上次操作受影响的条数
     * @return int
     */
    public function affected_rows()
    {
        return $this->lastdb ? $this->lastdb->affected_rows() : 0;
    }
    /**
     * @description: 获取数据库版本
     * @params string $assign 数据库连接对象
     * @return string
     */
    public function version($assign = "master")
    {
        $db = ($assign == "master") ? $this->master : $this->slave;
        return $db ? $db->version() : "";
    }
    /**
     * @description: 错误信息
     * @return string
     */
    public function error()
    {
        return $this->lastdb ? $this->lastdb->error() : "";
    }
    /**
     * @description: 错误编号
     * @return string
     */
    public function errno()
    {
        return $this->lastdb ? $this->lastdb->errno() : "";
    }
    /**
     * @description: 数据语句过滤
     * @param string $sql
     * @return {*}
     */
    public function filter($sql = "")
    {
        if (preg_match('/\b(union|sleep|benchmark|load_file|outfile)\b/i', $sql)) {
            LCMS::X(0, "SQL语句包含非法字符");
        }
    }
    /**
     * @description: 关闭数据库连接
     * @return {*}
     */
    public function close()
    {
        if ($this->slave) {
            $this->slave->close();
        }
        $this->master->close();
    }
    /**
     * @description: 设置参数绑定
     * @param array $bind
     * @param bool $clear
     * @return {*}
     */
    private function bindFilter($bind = [], $clear = true)
    {
        if (empty($bind) || !is_array($bind)) {
            $bind = [];
        }
        $i = 0;
        //处理 IN 查询中的数组绑定
        foreach ($bind as $key => $val) {
            if (is_array($val)) {
                $placeholder = (strpos($key, ':') !== 0) ? ":{$key}" : $key;
                $newParams   = [];
                foreach ($val as $v) {
                    $uniqueKey               = ":_in_" . $i;
                    $newParams[]             = $uniqueKey;
                    $this->_bind[$uniqueKey] = $v;
                    $i++;
                }
                if ($newParams) {
                    if ($this->_sql) {
                        $this->_sql = str_ireplace($placeholder, implode(", ", $newParams), $this->_sql);
                    }
                    if ($this->_where) {
                        $this->_where = str_ireplace($placeholder, implode(", ", $newParams), $this->_where);
                    }
                    if ($this->_order) {
                        $this->_order = str_ireplace($placeholder, implode(", ", $newParams), $this->_order);
                    }
                }
                unset($bind[$key]);
            }
        }
        //合并用户传入的普通参数到内部绑定池
        $this->_bind = array_merge($this->_bind, $bind);
        //提取占位符
        $params = [];
        $texts  = $this->_sql . " " . $this->_where . " " . $this->_order . " " . $this->_fields;
        //提取各种参数里的
        if (
            $texts &&
            preg_match_all("/:([a-zA-Z0-9_]+)/", $texts, $matches)
        ) {
            $params = $matches[0];
        }
        //提取DATA里的
        if (
            !empty($this->_data['vals']) &&
            is_array($this->_data['vals'])
        ) {
            array_walk_recursive($this->_data['vals'], function ($value) use (&$params) {
                if (
                    is_string($value) &&
                    preg_match_all("/:([a-zA-Z0-9_]+)/", $value, $_ms)
                ) {
                    $params = array_merge($params, $_ms[0]);
                }
            });
        }
        $params = array_unique($params);
        //剔除用户多传的参数
        if (!empty($params)) {
            foreach ($this->_bind as $key => $val) {
                if (
                    !in_array($key, $params) &&
                    strpos($key, ":_val_") !== 0 &&
                    strpos($key, ":_in_") !== 0
                ) {
                    unset($this->_bind[$key]);
                }
            }
        } elseif ($clear) {
            foreach ($this->_bind as $key => $val) {
                if (
                    strpos($key, ":_val_") !== 0 &&
                    strpos($key, ":_in_") !== 0
                ) {
                    unset($this->_bind[$key]);
                }
            }
        }

        //处理data()方法中特殊标记的数学函数占位符
        foreach ($this->_bind as $key => $val) {
            if ($val === "@LCMS_BIND") {
                unset($this->_bind[$key]);
            }
        }
    }
}
