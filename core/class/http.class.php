<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-13 15:26:20
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    /**
     * [get curl_get]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public static function get($url, $header = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        $r = $header ? [
            "code"   => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            "type"   => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            "length" => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
            "body"   => $r,
        ] : $r;
        curl_close($ch);
        return $r;
    }
    /**
     * [post description]
     * @param  [type]  $url   [description]
     * @param  [type]  $data  [description]
     * @param  boolean $build [是否进行 http_build_query]
     * @return [type]         [description]
     */
    public static function post($url, $data, $build = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $build ? http_build_query($data) : $data);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
