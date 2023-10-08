<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-10-07 14:20:09
 * @LastEditTime: 2023-10-07 19:33:20
 * @Description: 短链系统
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SHORTLINK
{
    /**
     * @description: 创建短连接
     * @param array $payload [url, data, time]
     * @return string
     */
    public static function create($payload = [])
    {
        global $_L;
        self::clearLink();
        $payload['url'] || LCMS::X(403, "缺少链接地址");
        $payload['data'] = $payload['data'] ?: [];
        if ($payload['time'] === 0) {
            $payload['time'] = 4133951999;
        } elseif (!$payload['time']) {
            $payload['time'] = time() + 86400;
        } else {
            $payload['time'] = time() + $payload['time'];
        }
        $hval = md5($payload['url'] . json_encode($payload['data']));
        $link = self::checkHval($hval);
        if ($link) {
            //如果数据存在，更新到期时间
            $link['lasttime'] = $payload['time'];
            sql_update([
                "table" => "shortlink",
                "data"  => [
                    "lasttime" => $link['lasttime'],
                ],
                "where" => "id = :id",
                "bind"  => [
                    ":id" => $link['id'],
                ],
            ]);
        } else {
            //如果数据不存在，插入数据
            $link = self::addHval($hval, $payload);
        }
        if ($link) {
            $domain = $payload['domain'] ?: ($_L['url']['web']['site'] ?: $_L['url']['site']);
            return "{$domain}quick/link.php?{$link['code']}";
        }
        return false;
    }
    /**
     * @description: 获取链接
     * @param string $code
     * @param string $domain
     * @return string
     */
    public static function get($code = "")
    {
        global $_L;
        self::clearLink();
        if ($code && strlen($code) === 8) {
            $link = sql_getall([
                "table" => "shortlink",
                "where" => "code = :code",
                "limit" => 1,
                "bind"  => [
                    ":code" => $code,
                ],
            ]);
        }
        if ($link) {
            $link         = $link[0];
            $link['data'] = sql2arr($link['data']);
        }
        return $link ?: [];
    }
    /**
     * @description: 检查链接是否存在
     * @param string $hval
     * @return bool
     */
    private static function checkHval($hval)
    {
        global $_L;
        $link = sql_getall([
            "table" => "shortlink",
            "where" => "hval = :hval",
            "limit" => 1,
            "bind"  => [
                ":hval" => $hval,
            ],
        ]);
        return $link ? $link[0] : [];
    }
    /**
     * @description: 添加链接
     * @param string $hval
     * @param array $payload
     * @return array
     */
    private static function addHval($hval, $payload = [])
    {
        global $_L;
        $code = randstr(8);
        $link = [
            "code"     => $code,
            "hval"     => $hval,
            "url"      => $payload['url'],
            "data"     => arr2sql($payload['data']),
            "lasttime" => $payload['time'],
        ];
        sql_insert([
            "table" => "shortlink",
            "data"  => $link,
        ]);
        if (sql_error()) {
            $link = self::addHval($hval, $payload = []);
        }
        return $link ?: [];
    }
    /**
     * @description: 清除过期链接
     * @return {*}
     */
    private static function clearLink()
    {
        global $_L;
        sql_delete([
            "table" => "shortlink",
            "where" => "lasttime < :lasttime",
            "bind"  => [
                ":lasttime" => time(),
            ],
        ]);
    }
}
