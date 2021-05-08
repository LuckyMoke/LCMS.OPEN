<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2021-05-07 21:07:44
 * @Description: mysql数据库操作方法
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * [sql_tablename 获取表名]
 * @param  [type] $table [description]
 * @return [type]        [description]
 */
function sql_tablename($table)
{

    global $_L;
    return $_L['mysql']['pre'] . $table;
}
/**
 * @description: 查询一条数据
 * @param array $sql [table, where, order, para, fields]
 * @return array|null
 */
function sql_get($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = $sql[1] ? " WHERE {$sql[1]}" : "";
    $order = $sql[2] ? " ORDER BY {$sql[2]}" : "";
    if ($sql[3] && is_array($sql[3])) {
        foreach ($sql[3] as $key => $val) {
            $where = str_replace($key, $val, $where);
        }
    }
    $fields = $sql[4] ? (is_array($sql[4]) ? implode(", ", $sql[4]) : $sql[4]) : "*";
    $query  = "SELECT {$fields} FROM {$table}{$where}{$order}";
    return DB::$mysql->get_one($query);
}
/**
 * @description: 查询多条数据
 * @param array $sql [table, where, order, para, key, fields, limit]
 * @return array|null
 */
function sql_getall($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = $sql[1] ? " WHERE {$sql[1]}" : "";
    $order = $sql[2] ? " ORDER BY {$sql[2]}" : "";
    if ($sql[3] && is_array($sql[3])) {
        foreach ($sql[3] as $key => $val) {
            $where = str_replace($key, $val, $where);
        }
    }
    $fields = $sql[5] ? (is_array($sql[5]) ? implode(", ", $sql[5]) : $sql[5]) : "*";
    $limit  = $sql[6] ? (is_array($sql[6]) ? " LIMIT {$sql[6][0]}, {$sql[6][1]}" : " LIMIT 0, {$sql[6]}") : "";
    $query  = "SELECT {$fields} FROM {$table}{$where}{$order}{$limit}";
    $mysql  = DB::$mysql->get_all($query);
    if ($sql[4] && $mysql) {
        foreach ($mysql as $key => $val) {
            $result[$val[$sql[4]]] = $val;
        }
        return $result;
    }
    return $mysql;
}
/**
 * @description: 获取字段统计
 * @param array $sql [table, where, order, para, fields, limit]
 * @return array|null
 */
function sql_total($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = $sql[1] ? " WHERE {$sql[1]}" : "";
    $order = $sql[2] ? " ORDER BY {$sql[2]}" : "";
    if ($sql[3] && is_array($sql[3])) {
        foreach ($sql[3] as $key => $val) {
            $where = str_replace($key, $val, $where);
        }
    }
    $fields = $sql[4] ? (is_array($sql[4]) ? implode(", ", $sql[4]) : $sql[4]) : "*";
    $limit  = $sql[5] ? (is_array($sql[5]) ? " LIMIT {$sql[5][0]}, {$sql[5][1]}" : " LIMIT 0, {$sql[5]}") : "";
    $query  = "(SELECT * FROM {$table}{$where}{$order}{$limit}) AS a";
    $fields = str_replace(["SUM(", "COUNT(", "AVG(", "MAX(", "MIN("], ["SUM(a.", "COUNT(a.", "AVG(a.", "MAX(a.", "MIN(a."], $fields);
    $query  = "SELECT {$fields} FROM {$query}";
    $result = DB::$mysql->get_all($query);
    return $result ? $result[0] : [];
}
/**
 * @description: 更新表数据
 * @param array $sql [table, data, where, para, math]
 * @return {*}
 */
function sql_update($sql = [])
{
    $params = [];
    $index  = 0;
    $table  = sql_tablename($sql[0]);
    if ($sql[1] && is_array($sql[1])) {
        foreach ($sql[1] as $key => $val) {
            $nval = ":sqlval_{$index}";
            if ($sql[4][$key]) {
                $data[] = "`{$key}` = {$key} {$sql[4][$key]} {$nval}";

                $params[$nval] = "{$val}";
            } else {
                $data[] = "`{$key}` = {$nval}";

                $params[$nval] = $val;
            }
            $index++;
        }
        $data = implode(", ", $data);
    } else {
        $data = $sql[1];
    }
    if ($data) {
        $where = $sql[2] ? " WHERE {$sql[2]}" : "";
        if ($sql[3] && is_array($sql[3])) {
            $params = array_merge($params, $sql[3]);
        }
        $query = "UPDATE `{$table}` SET {$data}{$where}";
        DB::$mysql->update($query, $params);
    }
}
/**
 * @description: 添加表数据
 * @param array $sql [table, data, para]
 * @return int|string|null
 */
function sql_insert($sql = [])
{
    $params = [];
    $index  = 0;
    $table  = sql_tablename($sql[0]);
    if ($sql[1][0]) {
        foreach ($sql[1] as $val) {
            $sql_key = array_keys($val);
            $nkey    = [];
            foreach ($val as $v) {
                $nval   = ":sqlval_{$index}";
                $nkey[] = $nval;

                $params[$nval] = $v;
                $index++;
            }
            $sql_val[] = implode(", ", $nkey);
        }
        $sql_val = "(" . implode("), (", $sql_val) . ")";
    } else {
        $sql_key = array_keys($sql[1]);
        $nkey    = [];
        foreach ($sql[1] as $v) {
            $nval   = ":sqlval_{$index}";
            $nkey[] = $nval;

            $params[$nval] = $v;
            $index++;
        }
        $sql_val = "(" . implode(", ", $nkey) . ")";
    }
    $sql_key = "(`" . implode("`, `", $sql_key) . "`)";
    if ($sql[2] && is_array($sql[2])) {
        $params = array_merge($params, $sql[2]);
    }
    $query = "INSERT IGNORE INTO `{$table}` {$sql_key} VALUES {$sql_val}";
    DB::$mysql->insert($query, $params);
    return sql_insert_id();
}
/**
 * @description: 删除表数据
 * @param array $sql [table, where]
 * @return {*}
 */
function sql_delete($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = $sql[1] ? " WHERE {$sql[1]}" : "";
    $query = "DELETE FROM {$table}{$where}";
    DB::$mysql->delete($query);
}
/**
 * @description: 获取数据量
 * @param array $sql [table, where, para]
 * @return int|string|null
 */
function sql_counter($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = trim($sql[1]);
    $where = $where ? " WHERE {$where}" : "";
    if ($sql[2] && is_array($sql[2])) {
        foreach ($sql[2] as $key => $val) {
            $where = str_replace($key, $val, $where);
        }
    }
    return DB::$mysql->counter("SELECT COUNT(*) FROM {$table}{$where}");
}
/**
 * @description: 自己写SQL语句
 * @param string $sql
 * @return {*}
 */
function sql_query($sql = "")
{
    return DB::$mysql->query($sql);
}
/**
 * @description: 获取插入的最后一个ID
 * @param {*}
 * @return int|string|null
 */
function sql_insert_id()
{
    return DB::$mysql->insert_id();
}
/**
 * @description: 返回上一次操作影响的条数
 * @param {*}
 * @return int|string|null
 */
function sql_affected_rows()
{
    return DB::$mysql->affected_rows();
}
/**
 * @description: 返回数据库操作错误
 * @param {*}
 * @return string
 */
function sql_error()
{
    return DB::$mysql->error();
}
/**
 * @description: 返回数据库操作错误编号
 * @param {*}
 * @return string|int
 */
function sql_errno()
{
    return DB::$mysql->errno();
}
