<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-08-25 12:04:52
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
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $out);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $result = $out ? [
            "code"   => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            "type"   => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            "length" => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            "body"   => $result,
        ] : $result;
        curl_close($ch);
        return $result;
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
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $build ? http_build_query($data) : $data);
        $result = curl_exec($ch);
        $hSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $hBody  = self::getHeaders(substr($result, 0, $hSize));
        $result = substr($result, $hSize);
        if (in_string($hBody[0], [
            "301", "302", "303", "307", "308",
        ])) {
            $result = self::post($hBody['location'], $data, $build, $headers);
        }
        return $result;
    }
    /**
     * @description: 获取响应头数组
     * @param string $hBody
     * @return array
     */
    private static function getHeaders($hBody = "")
    {
        $hBody = str_replace("\r\n", "\n", $hBody);
        $hBody = explode("\n", $hBody);
        $hBody = array_filter($hBody);
        $hBody = $hBody ?: [];
        $head  = [];
        foreach ($hBody as $i => $v) {
            if ($i == 0) {
                $head[0] = $v;
            } else {
                $c                       = strpos($v, ": ");
                $head[substr($v, 0, $c)] = substr($v, $c + 2);
            }
        }
        return $head;
    }
}
