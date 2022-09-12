<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-09-08 17:27:56
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    public static $TIMEOUT  = 30;
    private static $HEADERS = [];
    private static $METHOD  = "";
    private static $INFO    = [];
    private static $CH;
    /**
     * @description: HTTP GET
     * @param string $url 请求链接
     * @param bool $out 是否输出头部信息
     * @param array $headers 携带头部内容
     * @return string|array|null
     */
    public static function get($url, $out = false, $headers = [])
    {
        self::$CH      = curl_init($url);
        self::$METHOD  = "GET";
        self::$HEADERS = $headers;
        self::setBaseOpt();
        $result = self::getRequest();
        $result = $out ? [
            "code"   => self::$INFO['http_code'],
            "type"   => self::$INFO['content_type'],
            "length" => self::$INFO['size_download'],
            "body"   => $result,
        ] : $result;
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
    public static function post($url, $data = [], $build = false, $headers = [])
    {
        self::$CH      = curl_init($url);
        self::$METHOD  = "POST";
        self::$HEADERS = $headers;
        self::setBaseOpt($build ? http_build_query($data) : $data);
        $result = self::getRequest();
        if (in_array(self::$INFO['http_code'], [
            301, 302, 303, 307, 308,
        ])) {
            $result = self::post(self::$INFO['redirect_url'], $data, $build, $headers);
        }
        return $result;
    }
    /**
     * @description: HTTP DELETE
     * @param string $url 请求链接
     * @param array $headers 携带头部内容
     * @return {*}
     */
    public static function delete($url, $headers = [])
    {
        self::$CH      = curl_init($url);
        self::$METHOD  = "DELETE";
        self::$HEADERS = $headers;
        self::setBaseOpt();
        $result = self::getRequest();
        if (in_array(self::$INFO['http_code'], [
            301, 302, 303, 307, 308,
        ])) {
            $result = self::delete(self::$INFO['redirect_url'], $headers);
        }
        return $result;
    }
    /**
     * @description: HTTP PUT
     * @param string $url 请求链接
     * @param mixed $data 请求数据
     * @param array $headers 携带头部内容
     * @return {*}
     */
    public static function put($url, $data = false, $headers = [])
    {
        self::$CH      = curl_init($url);
        self::$METHOD  = "PUT";
        self::$HEADERS = $headers;
        self::setBaseOpt($data);
        $result = self::getRequest();
        if (in_array(self::$INFO['http_code'], [
            301, 302, 303, 307, 308,
        ])) {
            $result = self::put(self::$INFO['redirect_url'], $data, $headers);
        }
        return $result;
    }
    /**
     * @description: HTTP HEAD
     * @param string $url
     * @return array
     */
    public static function head($url)
    {
        self::$CH      = curl_init($url);
        self::$METHOD  = "HEAD";
        self::$HEADERS = [];
        self::setBaseOpt();
        self::getRequest();
        return self::$INFO;
    }
    /**
     * @description: 配置请求信息
     * @param mixed $data
     * @return {*}
     */
    private static function setBaseOpt($data = "")
    {
        if (self::$HEADERS) {
            foreach (self::$HEADERS as $k => $v) {
                $h[] = "{$k}: {$v}";
                $v == "gzip" && curl_setopt(self::$CH, CURLOPT_ENCODING, "gzip");
            }
            curl_setopt(self::$CH, CURLOPT_HTTPHEADER, $h);
        }
        curl_setopt(self::$CH, CURLOPT_TIMEOUT, self::$TIMEOUT);
        curl_setopt(self::$CH, CURLOPT_HEADER, false);
        curl_setopt(self::$CH, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36");
        curl_setopt(self::$CH, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt(self::$CH, CURLOPT_CUSTOMREQUEST, self::$METHOD);
        switch (self::$METHOD) {
            case 'GET':
                curl_setopt(self::$CH, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt(self::$CH, CURLINFO_HEADER_OUT, $data);
                break;
            case 'POST':
                curl_setopt(self::$CH, CURLOPT_POST, true);
                curl_setopt(self::$CH, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt(self::$CH, CURLOPT_POSTFIELDS, $data);
                break;
        }
    }
    /**
     * @description: 获取请求结果
     * @return mixed
     */
    private static function getRequest()
    {
        $body       = curl_exec(self::$CH);
        self::$INFO = curl_getinfo(self::$CH);
        curl_close(self::$CH);
        return $body;
    }
}
