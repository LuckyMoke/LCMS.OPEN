<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-04-16 11:10:16
 * @Description:HTTP请求
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class HTTP
{
    public static $TIMEOUT = 30;
    public static $HEADERS = [];
    /**
     * @description: 此方法已弃用
     */
    public static function get($url, $out = false, $headers = [])
    {
        $result = self::request([
            "type"    => "GET",
            "url"     => $url,
            "timeout" => self::$TIMEOUT,
            "headers" => $headers,
        ], $info, $header);
        $result = $out ? [
            "code"   => $info['http_code'],
            "type"   => $info['content_type'],
            "length" => $info['size_download'],
            "body"   => $result,
        ] : $result;
        self::$HEADERS = $header;
        self::resetOpt();
        return $result;
    }
    /**
     * @description: 此方法已弃用
     */
    public static function post($url, $data = [], $build = false, $headers = [])
    {
        $result = self::request([
            "type"    => "POST",
            "url"     => $url,
            "data"    => $data,
            "build"   => $build,
            "timeout" => self::$TIMEOUT,
            "headers" => $headers,
        ], $info, $header);
        self::$HEADERS = $header;
        self::resetOpt();
        return $result;
    }
    /**
     * @description: 此方法已弃用
     */
    private static function resetOpt()
    {
        self::$TIMEOUT = 30;
    }
    /**
     * @description: HTTP请求方法
     * @param array $args [type、url、ip、data、timeout、headers、proxy、ua、cache、setopt、success、error、complete]
     * @param array $curl_info curl信息
     * @param array $response_headers 响应标头
     * @return mixed
     */
    public static function request($args = [], &$curl_info = [], &$response_headers = [])
    {
        if (!$args['url']) {
            return false;
        }
        $args = array_merge([
            "type"     => "GET",
            "url"      => "",
            "data"     => "",
            "build"    => false,
            "timeout"  => 30,
            "headers"  => [],
            "proxy"    => [],
            "ua"       => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) PanQiFramework AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36",
            "setopt"   => null,
            "success"  => null,
            "error"    => null,
            "complete" => null,
        ], $args);
        $args['type'] = $args['method'] ?: $args['type'];
        $args['type'] = strtoupper($args['type'] ?: "GET");
        switch ($args['type']) {
            case 'GET':
            case 'DELETE':
                if (is_array($args['data'])) {
                    $query = http_build_query($args['data']);
                    if (in_string($args['url'], "?")) {
                        $args['url'] = trim($args['url'], "&");
                        $args['url'] .= "&{$query}";
                    } else {
                        $args['url'] .= "?{$query}";
                    }
                    unset($args['data']);
                }
                break;
            case 'POST':
            case 'PUT':
                if ($args['data']) {
                    if ($args['build'] && is_array($args['data'])) {
                        $args['data'] = http_build_query($args['data']);
                    }
                    if (
                        !is_array($args['data']) &&
                        !is_null(json_decode($args['data'])) &&
                        !$args['headers']['Content-Type']
                    ) {
                        $args['headers']['Content-Type'] = "application/json; charset=utf-8";
                    }
                }
                break;
            case 'HEAD':
                if (is_array($args['data'])) {
                    $query = http_build_query($args['data']);
                    if (in_string($args['url'], "?")) {
                        $args['url'] = trim($args['url'], "&");
                        $args['url'] .= "&{$query}";
                    } else {
                        $args['url'] .= "?{$query}";
                    }
                    unset($args['data']);
                }
                $reinfo = get_headers($args['url'], true, stream_context_create([
                    "http" => [
                        "method"     => "GET",
                        "header"     => $args['headers'],
                        "user_agent" => $args['ua'],
                        "timeout"    => $args['timeout'],
                    ],
                ]));
                if ($reinfo) {
                    $args['success'] && $args['success']($reinfo, $reinfo, []);
                } else {
                    $args['error'] && $args['error']($reinfo);
                }
                $args['complete'] && $args['complete']($reinfo);
                $response_headers = $reinfo;
                return $reinfo;
                break;
        }
        $CURL = curl_init($args['url']);
        if ($args['header']) {
            $args['headers'] = array_merge($args['headers'] || [], $args['header'] || []);
            unset($args['header']);
        }
        if ($args['headers'] && is_array($args['headers'])) {
            foreach ($args['headers'] as $k => $v) {
                $headers[] = "{$k}: {$v}";
                $v == "gzip" && curl_setopt($CURL, CURLOPT_ENCODING, "gzip");
            }
            curl_setopt($CURL, CURLOPT_HTTPHEADER, $headers);
        }
        if ($args['proxy']) {
            if (!is_array($args['proxy'])) {
                $args['proxy'] = [
                    "url" => $args['proxy'],
                ];
            }
            curl_setopt($CURL, CURLOPT_PROXY, $args['proxy']['url']);
            if ($args['proxy']['user'] && $args['proxy']['pass']) {
                curl_setopt($CURL, CURLOPT_PROXYUSERPWD, "{$args['proxy']['user']}:{$args['proxy']['pass']}");
            }
        }
        if ($args['cookie']) {
            curl_setopt($CURL, CURLOPT_COOKIE, $args['cookie']);
        }
        if ($args['ip']) {
            $urls    = parse_url($args['url']);
            $resolve = "{$urls['host']}:";
            if ($urls['port']) {
                $resolve .= $urls['port'];
            } elseif ($urls['scheme'] == "https") {
                $resolve .= 443;
            } else {
                $resolve .= 80;
            }
            $resolve .= ":{$args['ip']}";
            curl_setopt($CURL, CURLOPT_RESOLVE, [$resolve]);
        }
        curl_setopt($CURL, CURLOPT_TIMEOUT, $args['timeout']);
        curl_setopt($CURL, CURLOPT_USERAGENT, $args['ua']);
        curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($CURL, CURLOPT_SSL_VERIFYHOST, false);
        switch ($args['type']) {
            case 'GET':
                curl_setopt($CURL, CURLOPT_HEADER, true);
                curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($CURL, CURLOPT_FOLLOWLOCATION, true);
                $return = true;
                break;
            case 'POST':
                curl_setopt($CURL, CURLOPT_HEADER, true);
                curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($CURL, CURLOPT_POST, true);
                curl_setopt($CURL, CURLOPT_POSTFIELDS, $args['data']);
                $return = true;
                break;
            case 'PUT':
                curl_setopt($CURL, CURLOPT_HEADER, true);
                curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($CURL, CURLOPT_POSTFIELDS, $args['data']);
                $return = true;
                break;
            case 'DELETE':
                curl_setopt($CURL, CURLOPT_HEADER, true);
                curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "DELETE");
                $return = true;
                break;
            case 'DOWNLOAD':
                if (!$args['file']) {
                    return "";
                }
                delfile($args['file']);
                $downfile = fopen($args['file'], "w+");
                curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($CURL, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($CURL, CURLOPT_FILE, $downfile);
                $return = false;
                break;
        }
        $args['setopt'] && $args['setopt']($CURL);
        if ($return) {
            $body   = curl_exec($CURL);
            $reinfo = curl_getinfo($CURL);
            curl_close($CURL);
            if ($body) {
                $hinfo = substr($body, 0, $reinfo['header_size']);
                $hinfo = str_replace("\r\n", "\n", $hinfo);
                $hinfo = explode("\n", trim($hinfo));
                unset($hinfo[0]);
                $rehead = [
                    "code" => $reinfo['http_code'],
                ];
                foreach ($hinfo as $info) {
                    $infos    = explode(':', $info, 2);
                    $infos[1] = trim($infos[1]);
                    if ($infos[1]) {
                        $rehead = array_merge($rehead, [
                            trim($infos[0]) => $infos[1],
                        ]);
                    }
                }
                switch ($args['type']) {
                    case 'POST':
                    case 'PUT':
                    case 'DELETE':
                        if (in_array($reinfo['http_code'], [
                            301, 302, 303, 307, 308,
                        ])) {
                            if ($reinfo['redirect_url']) {
                                return self::request(array_merge($args, [
                                    "url" => $reinfo['redirect_url'],
                                ]), $curl_info, $response_headers);
                            }
                        }
                        break;
                }
                $result = substr($body, $reinfo['header_size'], strlen($body));
                $args['success'] && $args['success']($result, $reinfo, $rehead);
                $response_headers = $rehead;
            } else {
                $args['error'] && $args['error']($reinfo);
            }

        } else {
            curl_exec($CURL);
            $reinfo = curl_getinfo($CURL);
            curl_close($CURL);
            switch ($args['type']) {
                case 'DOWNLOAD':
                    fclose($downfile);
                    if ($reinfo['http_code'] == 200) {
                        $result = [
                            "code" => 1,
                            "file" => $args['file'],
                        ];
                        $args['success'] && $args['success']($result, $reinfo, []);
                    } else {
                        delfile($args['file']);
                        $args['error'] && $args['error']($reinfo);
                        $result = "";
                    }
                    break;
            }
        }
        $args['complete'] && $args['complete']($reinfo);
        $curl_info = $reinfo;
        return $result ?: "";
    }
}
