<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2020-08-09 16:53:38
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
 * [sql_get 查询一条数据]
 * @param  [type] $sql  [table, where, order, para, fields]
 * @return [type]       [description]
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
 * [sql_getall 查询多条数据]
 * @param  [type] $sql  [table, where, order, para, key, fields, limit]
 * @return [type]       [description]
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
 * [sql_total 获取字段统计]
 * @param  [type] $sql [table, where, order, para, fields, limit]
 * @return [type]      [description]
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
    return $result[0];
}
/**
 * [sql_update 更新表]
 * @param  [type] $sql [table, data, where, para, math]
 * @return [type]      [description]
 */
function sql_update($sql = [])
{
    $table = sql_tablename($sql[0]);
    if ($sql[1] && is_array($sql[1])) {
        foreach ($sql[1] as $key => $val) {
            if ($sql[4][$key]) {
                $data[] = "{$key} = {$key} {$sql[4][$key]} {$val}";
            } else {
                $val    = $val !== 0 && $val !== '0' && empty($val) ? "[LCMSSQLNULL]" : $val;
                $data[] = "{$key} = '{$val}'";
            }
        }
        $data = implode(",", $data);
    } else {
        $data = $sql[1];
    }
    if ($data) {
        $where = $sql[2] ? " WHERE {$sql[2]}" : "";
        if ($sql[3] && is_array($sql[3])) {
            foreach ($sql[3] as $key => $val) {
                $where = str_replace($key, $val, $where);
            }
        }
        $data  = str_replace("'[LCMSSQLNULL]'", "NULL", $data);
        $query = "UPDATE {$table} SET {$data}{$where}";
        DB::$mysql->update($query);
    }
}
/**
 * [sql_insert 添加表]
 * @param  [type] $sql [table, data, para]
 * @return [type]      [description]
 */
function sql_insert($sql = [])
{
    $table = sql_tablename($sql[0]);
    if ($sql[1][0]) {
        foreach ($sql[1] as $val) {
            $sql_key = array_keys($val);
            foreach ($val as $index => $v) {
                $val[$index] = $val !== 0 && $v !== '0' && empty($v) ? "[LCMSSQLNULL]" : $v;
            }
            $sql_val[] = implode("','", array_values($val));
        }
        $sql_val = "('" . implode("'),('", $sql_val) . "')";
    } else {
        $sql_key = array_keys($sql[1]);
        foreach ($sql[1] as $index => $v) {
            $sql[1][$index] = $val !== 0 && $v !== '0' && empty($v) ? "[LCMSSQLNULL]" : $v;
        }
        $sql_val = "('" . implode("','", array_values($sql[1])) . "')";
    }
    $sql_key = "(" . implode(",", $sql_key) . ")";
    if ($sql[2] && is_array($sql[2])) {
        foreach ($sql[2] as $key => $val) {
            $sql_val = str_replace($key, $val, $sql_val);
        }
    }
    $sql_val = str_replace("'[LCMSSQLNULL]'", "NULL", $sql_val);
    $query   = "INSERT IGNORE INTO {$table} {$sql_key} VALUES {$sql_val}";
    DB::$mysql->insert($query);
    return sql_insert_id();
}
/**
 * [sql_delete 删除数据]
 * @param  [type] $sql [table, where]
 * @return [type]      [description]
 */
function sql_delete($sql = [])
{
    $table = sql_tablename($sql[0]);
    $where = $sql[1] ? " WHERE {$sql[1]}" : "";
    $query = "DELETE FROM {$table}{$where}";
    DB::$mysql->delete($query);
}
/**
 * [sql_counter 获取数据量]
 * @param  [type] $sql [table, where, para]
 * @return [type]      [description]
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
 * [sql_query 自己写SQL语句]
 * @param  string $sql [query]
 * @return [type]      [description]
 */
function sql_query($sql = "")
{
    return DB::$mysql->query($sql);
}
/**
 * [sql_insert_id 获取插入的最后一个ID]
 * @return [type] [description]
 */
function sql_insert_id()
{
    return DB::$mysql->insert_id();
}
/**
 * [sql_affected_rows 返回上一次操作影响的条数]
 * @return [type] [description]
 */
function sql_affected_rows()
{
    return DB::$mysql->affected_rows();
}
/**
 * [sql_error 返回数据库操作错误]
 * @return [type] [description]
 */
function sql_error()
{
    return DB::$mysql->error();
}
/**
 * [sql_errno 返回数据库操作错误编号]
 * @return [type] [description]
 */
function sql_errno()
{
    return DB::$mysql->errno();
}
