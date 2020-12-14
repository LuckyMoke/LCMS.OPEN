<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-13 16:07:13
 * @Description:头条小程序接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class MP
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
        $this->cache();
    }
    /**
     * [cache 数据缓存读取与保存]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function cache($type = "get")
    {
        if ($this->cfg['appid'] && $this->cfg['appsecret']) {
            $cachename = md5($this->cfg['appid'] . $this->cfg['appsecret']);
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cache([
                    "name" => $cachename,
                    "data" => $this->cfg,
                ]);
                break;
            default:
                $cache = LCMS::cache([
                    "name" => $cachename,
                ]);
                $this->cfg = is_array($cache) ? array_merge($this->cfg, $cache) : $this->cfg;
                break;
        }
    }
    /**
     * [openid 通过登陆code获取用户OPENID]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function openid($code)
    {
        $query = http_build_query([
            "appid"  => $this->cfg['appid'],
            "secret" => $this->cfg['appsecret'],
            "code"   => $code,
        ]);
        $result = json_decode(http::get("https://developer.toutiao.com/api/apps/jscode2session?{$query}"), true);
        return $result;
    }
    /**
     * [access_token 生成access_token缓存]
     * @return [type] [description]
     */
    public function access_token()
    {
        if (!$this->cfg['access_token']['token'] || $this->cfg['access_token']['expires'] < time()) {
            $query = http_build_query(array(
                "appid"      => $this->cfg['appid'],
                "secret"     => $this->cfg['appsecret'],
                "grant_type" => "client_credential",
            ));
            $token = json_decode(http::get("https://developer.toutiao.com/api/apps/token?{$query}"), true);
            if ($token['errcode']) {
                return $token;
            } else {
                $this->cfg['access_token'] = [
                    "token"   => $token['access_token'],
                    "expires" => time() + 3600,
                ];
                $this->cache("save");
            }
        }
    }
    /**
     * [qrcode 获取小程序码]
     * @param  [type] $path    [description]
     * @param  string $appname [description]
     * @return [type]          [description]
     */
    public function qrcode($path, $appname = "douyin")
    {
        $this->access_token();
        $result = $this->post_json("https://developer.toutiao.com/api/apps/qrcode", json_encode_ex([
            "access_token" => $this->cfg['access_token']['token'],
            "appname"      => $appname,
            "path"         => urlencode($path),
        ]));
        if ($result && is_array(json_decode($result, true))) {
            $result = json_decode($result, true);
        }
        return $result;
    }
    /**
     * [post_json 发送json数据]
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function post_json($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
