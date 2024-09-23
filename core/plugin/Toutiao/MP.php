<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-09-21 23:22:39
 * @Description: 头条小程序接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class MP
{
    public $CFG = [];
    public function __construct($config)
    {
        $this->CFG = [
            "appid"     => $config['appid'],
            "appsecret" => $config['appsecret'],
        ];
        $this->cache();
    }
    /**
     * @description: 数据缓存读取与保存
     * @param string $type
     * @return {*}
     */
    public function cache($type = "get")
    {
        if ($this->CFG['appid'] && $this->CFG['appsecret']) {
            $cname = $this->CFG['appid'] . $this->CFG['appsecret'];
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cache($cname, $this->CFG);
                break;
            case 'clear':
                LCMS::cache($cname, "clear");
                break;
            default:
                $arr = LCMS::cache($cname);
                if (is_array($arr)) {
                    $this->CFG = array_merge($arr, $this->CFG);
                }
                break;
        }
    }
    /**
     * @description: 生成access_token缓存
     * @return {*}
     */
    public function access_token()
    {
        $this->cache();
        if (!$this->CFG['access_token'] || $this->CFG['access_token']['expires_in'] < time()) {
            $token = HTTP::request([
                "type"    => "POST",
                "url"     => "https://open.douyin.com/oauth/client_token/",
                "data"    => json_encode([
                    "grant_type"    => "client_credential",
                    "client_key"    => $this->CFG['appid'],
                    "client_secret" => $this->CFG['appsecret'],
                ]),
                "headers" => [
                    "Content-Type" => "application/json",
                ],
            ]);
            $token = json_decode($token, true);
            if ($token['data']['access_token']) {
                $token                     = $token['data'];
                $this->CFG['access_token'] = [
                    "access_token" => $token['access_token'],
                    "expires_in"   => $token['expires_in'] + time() - 300,
                ];
                $this->cache("save");
            } else {
                return $token;
            }
        }
        return $this->CFG['access_token'] ?: [];
    }
    /**
     * @description: 通过登录code获取用户OPENID
     * @param string $type
     * @param string $code
     * @return {*}
     */
    public function openid($type = "code", $code)
    {
        $result = HTTP::request([
            "type"    => "POST",
            "url"     => "https://developer.toutiao.com/api/apps/v2/jscode2session",
            "data"    => json_encode([
                "appid"  => $this->CFG['appid'],
                "secret" => $this->CFG['appsecret'],
                $type    => $code,
            ]),
            "headers" => [
                "Content-Type" => "application/json",
            ],
        ]);
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 获取小程序码
     * @param string $path
     * @param string $appname
     * @return {*}
     */
    public function get_qrcode($path, $appname = "douyin")
    {
        $this->access_token();
        $result = HTTP::request([
            "type"    => "POST",
            "url"     => "https://open.douyin.com/api/apps/v1/qrcode/create/",
            "data"    => json_encode([
                "appid"    => $this->CFG['appid'],
                "app_name" => $appname,
                "path"     => urlencode($path),
            ]),
            "headers" => [
                "Content-Type" => "application/json",
                "Access-Token" => $this->CFG['access_token']['access_token'],
            ],
        ]);
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 获取小程序链接
     * @param string $path
     * @param string $query
     * @param string $appname
     * @return {*}
     */
    public function get_urllink($path, $query = "{}", $appname = "douyin")
    {
        $this->access_token();
        $result = HTTP::request([
            "type"    => "POST",
            "url"     => "https://open.douyin.com/api/apps/v1/url_link/generate/",
            "data"    => json_encode([
                "app_id"      => $this->CFG['appid'],
                "app_name"    => $appname,
                "expire_time" => time() + 86400,
                "path"        => $path,
                "query"       => $query,
            ]),
            "headers" => [
                "Content-Type" => "application/json",
                "Access-Token" => $this->CFG['access_token']['access_token'],
            ],
        ]);
        $result = json_decode($result, true);
        return $result ?: [];
    }
}
