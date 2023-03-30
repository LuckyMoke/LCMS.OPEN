<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-03-30 12:41:23
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
}
load::sys_func('mysql');
