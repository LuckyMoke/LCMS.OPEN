<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-06-11 15:33:17
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    /**
     * @description: HTTP GET
     * @param string $url 请求链接
     * @param bool $out 是否输出头部信息
     * @param array $headers 携带头部内容
     * @return string|array|null
     */
    public static function get($url, $out = false, $headers = [])
    {
        $ch = curl_init($url);
        if ($headers) {
            foreach ($headers as $key => $val) {
                $header[] = "{$key}: {$val}";
                if ($val == "gzip") {
                    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $out);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        $r = $out ? [
            "code"   => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            "type"   => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            "length" => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            "body"   => $r,
        ] : $r;
        curl_close($ch);
        return $r;
    }
    /**
     * @description: HTTP POST
     * @param string $url 请求链接
     * @param mixed $data 请求数据
     * @param bool $build 是否转换data数组
     * @param array $headers 携带头部内容
     * @return string|null
     */
    public static function post($url, $data, $build = false, $headers = [])
    {
        $ch = curl_init($url);
        if ($headers) {
            foreach ($headers as $key => $val) {
                $header[] = "{$key}: {$val}";
                if ($val == "gzip") {
                    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $build ? http_build_query($data) : $data);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
