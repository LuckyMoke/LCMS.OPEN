<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-13 15:27:41
 * @Description:Mysql数据库连接类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('sqlpdo');
class DB
{
    public static $mysql;
    /**
     * [dbconn 连接数据库]
     * @param  [type] $db [数据库信息]
     * @return [type]     [description]
     */
    public static function dbconn($db)
    {
        self::$mysql = new SQLPDO("mysql:host={$db['host']};dbname={$db['name']};port={$db['port']};charset={$db['charset']}", $db['user'], $db['pass']);
    }
    /**
     * [以下方法兼容老应用，逐步淘汰中，请勿使用！！！！]
     * [以下方法兼容老应用，逐步淘汰中，请勿使用！！！！]
     * [以下方法兼容老应用，逐步淘汰中，请勿使用！！！！]
     * [以下方法兼容老应用，逐步淘汰中，请勿使用！！！！]
     * [以下方法兼容老应用，逐步淘汰中，请勿使用！！！！]
     */
    public static function get_one($sql, $type = '')
    {
        return self::$mysql->get_one($sql);
    }
    public static function get_all($sql, $type = '')
    {
        return self::$mysql->get_all($sql);
    }
    public static function query($sql)
    {
        self::$mysql->query($sql);
    }
    public static function sql_get_one($table, $where = '', $order = '')
    {
        global $_L;
        $table = $_L['table'][$table];
        $where = $where ? " WHERE {$where}" : "";
        $order = $order ? " ORDER BY {$order}" : "";
        $query = "SELECT * FROM {$table}{$where}{$order}";
        return self::get_one($query);
    }
    public static function sql_get_all($table, $where = '', $order = '', $limitmin = '', $limitmax = '')
    {
        global $_L;
        $table = $_L['table'][$table];
        $where = $where ? " WHERE {$where}" : "";
        $order = $order ? " ORDER BY {$order}" : "";
        if ($limitmax) {
            $limit = $limitmax ? " LIMIT {$limitmin},{$limitmax}" : " LIMIT 0,{$limitmin}";
        }
        $query = "SELECT * FROM {$table}{$where}{$order}{$limit}";
        return self::get_all($query);
    }
    public static function sql_update($table, $arr, $where)
    {
        global $_L;
        $table = $_L['table'][$table];
        $where = " WHERE {$where}";
        foreach ($arr as $key => $val) {
            $sql_data[] = $key . " = '" . $val . "'";
        }
        $sql_data = implode(",", $sql_data);
        $query    = "UPDATE {$table} SET {$sql_data}{$where}";
        self::query($query);
    }
    public static function sql_insert($table, $arr)
    {
        global $_L;
        $table = $_L['table'][$table];
        if ($arr[1]) {
            foreach ($arr as $val) {
                $sql_key   = array_keys($val);
                $sql_val[] = implode("','", array_values($val));
            }
            $sql_val = "('" . implode("'),('", $sql_val) . "')";
        } else {
            $sql_key = array_keys($arr);
            $sql_val = array_values($arr);
            $sql_val = "('" . implode("','", $sql_val) . "')";
        }
        $sql_key = "(" . implode(",", $sql_key) . ")";
        $query   = "INSERT IGNORE INTO {$table} {$sql_key} VALUES {$sql_val}";
        self::query($query);
    }
    public static function sql_delete($table, $where = '')
    {
        global $_L;
        $table = $_L['table'][$table];
        $where = $where ? " WHERE {$where}" : "";
        $query = "DELETE FROM {$table}{$where}";
        self::query($query);
    }
}
load::sys_func('mysql');
