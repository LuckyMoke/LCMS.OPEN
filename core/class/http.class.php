<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-07-04 13:10:53
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    private static $CH;
    private static $HEADER    = [];
    private static $METHOD    = "";
    private static $USERAGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) PanQiFramework AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36";
    public static $TIMEOUT    = 30;
    public static $PROXY      = [];
    public static $INFO       = [];
    public static $HEADERS    = [];
    public static $UA         = "";
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
        self::resetOpt();
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
            if (self::$INFO['redirect_url']) {
                $result = self::post(self::$INFO['redirect_url'], $data, $build, $headers);
            }
        }
        self::resetOpt();
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
            if (self::$INFO['redirect_url']) {
                $result = self::delete(self::$INFO['redirect_url'], $headers);
            }
        }
        self::resetOpt();
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
            if (self::$INFO['redirect_url']) {
                $result = self::put(self::$INFO['redirect_url'], $data, $headers);
            }
        }
        self::resetOpt();
        return $result;
    }
    /**
     * @description: HTTP HEAD
     * @param string $url
     * @return array
     */
    public static function head($url, $headers = [])
    {
        self::$INFO = get_headers($url, true, stream_context_create([
            "http" => array(
                "method"     => "GET",
                "header"     => $headers,
                "user_agent" => self::$UA ?: self::$USERAGENT,
                "timeout"    => self::$TIMEOUT,
            ),
        ]));
        self::resetOpt();
        return self::$INFO;
    }
    /**
     * @description: 下载文件
     * @param string $url
     * @param string $file
     * @param array $headers
     * @return array
     */
    public static function download($url, $file, $headers = [])
    {
        delfile($file);
        $cfile        = fopen($file, "w+");
        self::$CH     = curl_init($url);
        self::$METHOD = "GET";
        self::$HEADER = $headers;
        curl_setopt(self::$CH, CURLOPT_FILE, $cfile);
        self::setBaseOpt("lcms-http-download");
        curl_exec(self::$CH);
        self::$INFO = curl_getinfo(self::$CH);
        curl_close(self::$CH);
        fclose($cfile);
        self::resetOpt();
        if (self::$INFO['http_code'] == 200) {
            return [
                "code" => 1,
                "file" => $file,
            ];
        } else {
            delfile($file);
            return ["code" => 0];
        }
    }
    /**
     * @description: 配置请求信息
     * @param mixed $data
     * @return {*}
     */
    private static function setBaseOpt($data = "")
    {
        if (!is_array($data) && !is_null(json_decode($data)) && !self::$HEADER['Content-Type']) {
            self::$HEADER['Content-Type'] = "application/json; charset=utf-8";
        }
        if (self::$HEADER) {
            foreach (self::$HEADER as $k => $v) {
                $h[] = "{$k}: {$v}";
                $v == "gzip" && curl_setopt(self::$CH, CURLOPT_ENCODING, "gzip");
            }
            curl_setopt(self::$CH, CURLOPT_HTTPHEADER, $h);
        }
        if (self::$PROXY) {
            if (!is_array(self::$PROXY)) {
                self::$PROXY = [
                    "url" => self::$PROXY,
                ];
            }
            curl_setopt(self::$CH, CURLOPT_PROXY, self::$PROXY['url']);
            if (self::$PROXY['user'] && self::$PROXY['pass']) {
                curl_setopt(self::$CH, CURLOPT_PROXYUSERPWD, '"' . self::$PROXY['user'] . ':' . self::$PROXY['user'] . '"');
            }
        }
        curl_setopt(self::$CH, CURLOPT_TIMEOUT, self::$TIMEOUT);
        curl_setopt(self::$CH, CURLOPT_USERAGENT, self::$UA ?: self::$USERAGENT);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(self::$CH, CURLOPT_SSL_VERIFYHOST, false);
        if ($data != "lcms-http-download") {
            curl_setopt(self::$CH, CURLOPT_HEADER, true);
            curl_setopt(self::$CH, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(self::$CH, CURLOPT_CUSTOMREQUEST, self::$METHOD);
        }
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
    /**
     * @description: 重置配置信息
     * @return {*}
     */
    private static function resetOpt()
    {
        self::$TIMEOUT = 30;
        self::$PROXY   = [];
        self::$UA      = "";
    }
}
