<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-03-07 15:50:06
 * @LastEditTime: 2026-08-25 16:21:16
 * @Description: Mysql数据库操作方法
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * @description: 查询一条数据
 * @param array $sql [table, where, order, bind, fields]
 * @return array|null
 */
function sql_get($sql = [])
{
    global $_L;
    if (!$_L['DB']) return [];
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->where($sql['where'] ?: $sql[1])
        ->order($sql['order'] ?: $sql[2])
        ->fields($sql['fields'] ?: $sql[4])
        ->bind($sql['bind'] ?: $sql[3])
        ->select(1);
}
/**
 * @description: 查询多条数据
 * @param array $sql [table, where, order, bind, setkey, fields, limit]
 * @return array|null
 */
function sql_getall($sql = [])
{
    global $_L;
    if (!$_L['DB']) return [];
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->where($sql['where'] ?: $sql[1])
        ->order($sql['order'] ?: $sql[2])
        ->setkey($sql['setkey'] ?: $sql[4])
        ->fields($sql['fields'] ?: $sql[5])
        ->limit($sql['limit'] ?: $sql[6])
        ->bind($sql['bind'] ?: $sql[3])
        ->select();
}
/**
 * @description: 更新表数据
 * @param array $sql [table, data, where, bind, math]
 * @return {*}
 */
function sql_update($sql = [])
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->data($sql['data'] ?: $sql[1])
        ->where($sql['where'] ?: $sql[2])
        ->math($sql['math'] ?: $sql[4])
        ->bind($sql['bind'] ?: $sql[3])
        ->update();
}
/**
 * @description: 添加表数据
 * @param array $sql [table, data, bind]
 * @return int|string|null
 */
function sql_insert($sql = [])
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->data($sql['data'] ?: $sql[1])
        ->bind($sql['bind'] ?: $sql[2])
        ->insert();
}
/**
 * @description: 删除表数据
 * @param array $sql [table, where, bind, order, limit]
 * @return {*}
 */
function sql_delete($sql = [])
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->where($sql['where'] ?: $sql[1])
        ->order($sql['order'] ?: $sql[3])
        ->limit($sql['limit'] ?: $sql[4])
        ->bind($sql['bind'] ?: $sql[2])
        ->delete();
}
/**
 * @description: 获取字段统计
 * @param array $sql [table, where, order, bind, fields, limit]
 * @return array|null
 */
function sql_total($sql = [])
{
    global $_L;
    if (!$_L['DB']) return [];
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->where($sql['where'] ?: $sql[1])
        ->order($sql['order'] ?: $sql[2])
        ->fields($sql['fields'] ?: $sql[4])
        ->limit($sql['limit'] ?: $sql[5])
        ->bind($sql['bind'] ?: $sql[3])
        ->total();
}
/**
 * @description: 获取数据量
 * @param array $sql [table, where, bind, fields]
 * @return int|string|null
 */
function sql_counter($sql = [])
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']
        ->table($sql['table'] ?: $sql[0])
        ->where($sql['where'] ?: $sql[1])
        ->fields($sql['fields'] ?: $sql[3])
        ->bind($sql['bind'] ?: $sql[2])
        ->count();
}
/**
 * @description: 自己写SQL语句
 * @param array|string $sql [query, bind]
 * @return {*}
 */
function sql_query($sql = [])
{
    global $_L;
    if (!$_L['DB']) return;
    if (is_string($sql)) {
        $sql = [
            "query" => $sql,
            "bind"  => [],
        ];
    }
    return $_L['DB']
        ->table("@query")
        ->query($sql['query'], $sql['bind']);
}
/**
 * @description: 开始事务
 * @return {*}
 */
function sql_begin()
{
    global $_L;
    if (!$_L['DB']) return;
    $_L['DB']->begin();
}
/**
 * @description: 事务回滚
 * @return {*}
 */
function sql_rollback()
{
    global $_L;
    if (!$_L['DB']) return;
    $_L['DB']->rollback();
}
/**
 * @description: 提交事务
 * @return bool
 */
function sql_commit()
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']->commit();
}
/**
 * @description: 获取插入的最后一个ID
 * @param {*}
 * @return int|string|null
 */
function sql_insert_id()
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']->insert_id();
}
/**
 * @description: 返回上一次操作影响的条数
 * @param {*}
 * @return int|string|null
 */
function sql_affected_rows()
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']->affected_rows();
}
/**
 * @description: 返回数据库操作错误
 * @param {*}
 * @return string
 */
function sql_error()
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']->error();
}
/**
 * @description: 返回数据库操作错误编号
 * @param {*}
 * @return string|int
 */
function sql_errno()
{
    global $_L;
    if (!$_L['DB']) return;
    return $_L['DB']->errno();
}
/**
 * @description: 内容过滤
 * @param string $sql
 * @return {*}
 */
function sql_filter($sql = "")
{
    global $_L;
    if (!$_L['DB']) return;
    $_L['DB']->filter($sql);
}
