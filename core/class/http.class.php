<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-03-28 13:28:11
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    public static $TIMEOUT = 30;
    public static $INFO    = [];
    public static $HEADERS = [];
    private static $HEADER = [];
    private static $METHOD = "";
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
        self::$CH     = curl_init($url);
        self::$METHOD = "GET";
        self::$HEADER = $headers;
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
        self::$CH     = curl_init($url);
        self::$METHOD = "POST";
        self::$HEADER = $headers;
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
        self::$CH     = curl_init($url);
        self::$METHOD = "DELETE";
        self::$HEADER = $headers;
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
        self::$CH     = curl_init($url);
        self::$METHOD = "PUT";
        self::$HEADER = $headers;
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
        self::$CH     = curl_init($url);
        self::$METHOD = "HEAD";
        self::$HEADER = [];
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
        if (self::$HEADER) {
            foreach (self::$HEADER as $k => $v) {
                $h[] = "{$k}: {$v}";
                $v == "gzip" && curl_setopt(self::$CH, CURLOPT_ENCODING, "gzip");
            }
            curl_setopt(self::$CH, CURLOPT_HTTPHEADER, $h);
        }
        curl_setopt(self::$CH, CURLOPT_TIMEOUT, self::$TIMEOUT);
        curl_setopt(self::$CH, CURLOPT_HEADER, true);
        curl_setopt(self::$CH, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) PanQiFramework AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36");
        curl_setopt(self::$CH, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt(self::$CH, CURLOPT_CUSTOMREQUEST, self::$METHOD);
        switch (self::$METHOD) {
            case 'GET':
                curl_setopt(self::$CH, CURLOPT_FOLLOWLOCATION, true);
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
        $hInfo = substr($body, 0, self::$INFO['header_size']);
        $hInfo = str_replace("\r\n", "\n", $hInfo);
        $hInfo = explode("\n", trim($hInfo));
        unset($hInfo[0]);
        self::$HEADERS = [
            "code" => self::$INFO['http_code'],
        ];
        foreach ($hInfo as $info) {
            $infoArr       = explode(':', $info, 2);
            self::$HEADERS = array_merge(self::$HEADERS, [
                trim($infoArr[0]) => trim($infoArr[1]),
            ]);
        }
        return substr($body, self::$INFO['header_size'], strlen($body));
    }
}
