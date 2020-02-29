<?php
class DB
{
    public static $querynum = 0;
    public static $link;
    /**
     * [dbconn 连接数据库]
     * @param  [type] $db [数据库信息]
     * @return [type]     [description]
     */
    public static function dbconn($db)
    {
        self::$link = @new mysqli($db['host'], $db['user'], $db['pass'], "", $db['port']);
        if (self::$link->connect_error) {
            self::halt($db['host']);
        }
        self::$link->query("SET sql_mode=''");
        if ($db['charset']) {
            mysqli_set_charset(self::$link, $db['charset']);
        }
    }
    /**
     * [select_db 选择数据库]
     * @param  [type] $con_db_name [description]
     * @return [type]              [description]
     */
    public static function select_db($con_db_name)
    {
        return self::$link->select_db($con_db_name);
    }
    /**
     * [fetch_all 结果集中取得所有行作为关联数组]
     * @param  [type] $result      [description]
     * @param  [type] $result_type [description]
     * @return [type]              [description]
     */
    public static function fetch_all($result, $result_type = MYSQLI_ASSOC)
    {
        if ($result instanceof mysqli_result) {
            return $result->fetch_all($result_type);
        } else {
            self::errno();
        }
    }
    /**
     * [fetch_array 从结果集中取得一行作为关联数组]
     * @param  [type] $result      [description]
     * @param  [type] $result_type [description]
     * @return [type]              [description]
     */
    public static function fetch_array($result, $result_type = MYSQLI_ASSOC)
    {
        if ($result instanceof mysqli_result) {
            return $result->fetch_array($result_type);
        } else {
            self::errno();
        }
    }
    /**
     * [affected_rows 返回上一次操作影响的条数]
     * @return [type] [description]
     */
    public static function affected_rows()
    {
        return self::$link->affected_rows;
    }
    /**
     * [error 返回上一次操作的错误信息]
     * @return [type] [description]
     */
    public static function error()
    {
        return self::$link->error;
    }
    /**
     * [errno 返回上一次操作的错误编号]
     * @return [type] [description]
     */
    public static function errno()
    {
        return self::$link->errno;
    }
    /**
     * [free_result 释放结果内存]
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    public static function free_result($result)
    {
        if ($result instanceof mysqli_result) {
            return $result->free();
        } else {
            self::errno();
        }
    }
    /**
     * [insert_id 返回最后一个查询中自动生成的 ID]
     * @return [type] [description]
     */
    public static function insert_id()
    {
        return self::$link->insert_id;
    }
    /**
     * [fetch_row 从结果集中取得一行，并作为枚举数组返回]
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    public static function fetch_row($result)
    {
        if ($result instanceof mysqli_result) {
            return $result->fetch_row();
        } else {
            self::errno();
        }
    }
    /**
     * [version 返回 MySQL 服务器版本]
     * @return [type] [description]
     */
    public static function version()
    {
        return @self::$link->server_info;
    }
    /**
     * [close 关闭数据库]
     * @return [type] [description]
     */
    public static function close()
    {
        return @self::$link->close();
    }
    /**
     * [halt 输出数据库错误]
     * @param  [type] $dbhost [description]
     * @return [type]         [description]
     */
    public static function halt($dbhost)
    {
        ajaxout(0, iconv('gbk', 'utf-8', self::$link->connect_error));
    }
    /**
     * [get_dbs 获取所有库名称]
     * @return [type] [description]
     */
    public static function get_dbs()
    {
        $result = self::query("SHOW DATABASES");
        $table  = self::fetch_all($result);
        foreach ($table as $val) {
            foreach ($val as $v) {
                $tables[] = $v;
            }
        }
        return $tables;
    }
    /**
     * [get_tables 获取数据库所有表]
     * @return [type] [description]
     */
    public static function get_tables()
    {
        $result = self::query("SHOW TABLES");
        $table  = self::fetch_all($result);
        foreach ($table as $val) {
            foreach ($val as $v) {
                $tables[] = $v;
            }
        }
        return $tables;
    }
    /**
     * [query 自操纵语句]
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    public static function query($sql)
    {
        if (!$result = self::$link->query($sql)) {
            self::errno();
        }
        return $result;
    }
}
