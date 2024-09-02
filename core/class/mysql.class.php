<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-03-07 15:50:06
 * @LastEditTime: 2024-08-26 19:23:39
 * @Description: Mysql数据库操作类
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class MYSQL
{
    public $db;
    private $config;
    private $master;
    private $slave;
    private $_table;
    private $_where;
    private $_order;
    private $_bind;
    private $_fields;
    private $_limit;
    private $_setkey;
    private $_data;
    private $_math;
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->db     = $this->master     = new SQLPDO("mysql:host={$config['host']};dbname={$config['name']};port={$config['port']};charset={$config['charset']}", $config['user'], $config['pass']);
        if ($config['slave']['host']) {
            $this->slave = new SQLPDO("mysql:host={$config['slave']['host']};dbname={$config['slave']['name']};port={$config['slave']['port']};charset={$config['charset']}", $config['slave']['user'], $config['slave']['pass']);
        }
    }
    /**
     * @description: 指定主从数据库
     * @param string $type
     * @return {*}
     */
    public function assign($type)
    {
        switch ($type) {
            case 'slave':
                $this->db = $this->slave;
                break;
            default:
                $this->db = $this->master;
                break;
        }
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
    public function bind($bind = [])
    {
        $this->bindFilter($bind);
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
            $datas = $data[0] ? $data : [$data];
            $i     = 0;
            $vals  = [];
            foreach ($datas as $index => $data) {
                foreach ($data as $key => $val) {
                    $nval               = ":_val{$i}";
                    $vals[$index][]     = $nval;
                    $this->_bind[$nval] = $val;
                    $i++;
                }
            }
            $this->_data = [
                "keys" => array_keys($datas[0]),
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
        $this->_math = $math;
        return $this;
    }
    /**
     * @description: 查询条数
     * @param string|array $limit
     * @return {*}
     */
    public function limit($limit = "")
    {
        $limit        = is_array($limit) ? implode(", ", $limit) : $limit;
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
    public function query($sql = "")
    {
        if (preg_match('/^SELECT.*/i', $sql)) {
            $this->slave && $this->assign("slave");
        }
        $result = $this->db->query($sql);
        $this->assign("master");
        return $result;
    }
    /**
     * @description: 查询操作
     * @param bool $one
     * @return array
     */
    public function select($one = false)
    {
        $query = "SELECT {$this->_fields} FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}";
        $this->slave && $this->assign("slave");
        if ($one) {
            $result = $this->db->get_one($query, $this->_bind);
        } else {
            $result = $this->db->get_all($query, $this->_bind);
            if ($this->_setkey && $result) {
                $result = array_column($result, null, $this->_setkey);
            }
        }
        $this->assign("master");
        return $result ?: [];
    }
    /**
     * @description: 更新操作
     * @return {*}
     */
    public function update()
    {
        foreach ($this->_data['keys'] as $index => $key) {
            $val = $this->_data['vals'][0][$index];
            if ($this->_math[$key]) {
                $val = "{$key} {$this->_math[$key]} {$val}";
            }
            $data[] = "`{$key}` = {$val}";
        }
        $data  = $data ? implode(", ", $data) : "";
        $query = "UPDATE {$this->_table} SET {$data}{$this->_where}";
        $this->db->update($query, $this->_bind);
    }
    /**
     * @description: 插入操作
     * @return {*}
     */
    public function insert()
    {
        foreach ($this->_data['vals'] as $index => $val) {
            $vals[] = implode(", ", $val);
        }
        $keys  = "(" . implode(", ", $this->_data['keys']) . ")";
        $vals  = "(" . implode("), (", $vals) . ")";
        $query = "INSERT IGNORE INTO {$this->_table} {$keys} VALUES {$vals}";
        $this->db->insert($query, $this->_bind);
        return $this->insert_id();
    }
    /**
     * @description: 删除操作
     * @return {*}
     */
    public function delete()
    {
        $query = "DELETE FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}";
        $this->db->delete($query, $this->_bind);
    }
    /**
     * @description: 统计条数
     * @return int
     */
    public function count()
    {
        $fields = $this->_fields != "*" ? $this->_fields : "";
        $fields = $fields ? "" : "COUNT(*)";
        $query  = "SELECT {$fields} FROM {$this->_table}{$this->_where}";
        $this->slave && $this->assign("slave");
        $count = $this->db->counter($query, $this->_bind);
        $this->assign("master");
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
        $query = "SELECT {$fields} FROM (SELECT * FROM {$this->_table}{$this->_where}{$this->_order}{$this->_limit}) AS a";
        $this->slave && $this->assign("slave");
        $result = $this->db->get_all($query, $this->_bind);
        $this->assign("master");
        return $result ? $result[0] : [];
    }
    /**
     * @description: 事务开始
     * @return {*}
     */
    public function begin()
    {
        $this->query("begin");
    }
    /**
     * @description: 事务回滚
     * @return {*}
     */
    public function rollback()
    {
        $this->query("rollback");
    }
    /**
     * @description: 事务提交
     * @return bool
     */
    public function commit()
    {
        if ($this->error()) {
            $this->rollback();
            return false;
        } else {
            $this->query("commit");
            return true;
        }
    }
    /**
     * @description: 获取所有数据表名
     * @return array
     */
    public function get_tables()
    {
        if ($this->slave) {
            return $this->slave->get_tables();
        }
        return $this->master->get_tables();
    }
    /**
     * @description: 获取上次插入数据的ID
     * @return int
     */
    public function insert_id()
    {
        return $this->db->insert_id();
    }
    /**
     * @description: 获取上次操作受影响的条数
     * @return int
     */
    public function affected_rows()
    {
        return $this->db->affected_rows();
    }
    /**
     * @description: 获取数据库版本
     * @return string
     */
    public function version()
    {
        $ver = $this->db ? $this->db->version() : "";
        $this->assign("master");
        return $ver;
    }
    /**
     * @description: 错误信息
     * @return string
     */
    public function error()
    {
        return $this->db->error();
    }
    /**
     * @description: 错误编号
     * @return string
     */
    public function errno()
    {
        return $this->db->errno();
    }
    /**
     * @description: 数据语句过滤
     * @param string $sql
     * @return {*}
     */
    public function filter($sql = "")
    {
        $result = str_ireplace([
            "select", "sleep", "union",
        ], [
            "sel/ect", "sl/eep", "un/ion",
        ], $sql);
        if ($result != $sql) {
            LCMS::X(0, "where语句非法");
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
     * @return {*}
     */
    private function bindFilter($bind = [])
    {
        $para  = [];
        $match = [];
        preg_match_all("/[\"|'|`|=| ](\:\w+)([\"|'|`| |)]|$)/i", $this->_where, $match);
        if ($match && $match[0]) {
            foreach ($match[0] as $index => $name) {
                $name = str_replace(")", "", trim($name));
                if ($name != $match[1][$index]) {
                    $names['old'][] = $name;
                    $names['new'][] = $match[1][$index];
                }
                $para[$match[1][$index]] = 1;
            }
            if ($names) {
                $this->_where = str_ireplace($names['old'], $names['new'], $this->_where);
            }
            if ($para) {
                $bind = array_intersect_key($bind, $para);
            }
        } else {
            $bind = [];
        }
        $this->_bind = array_merge($this->_bind, $bind ?: []);
    }
}
