<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2024-04-12 16:26:26
 * @LastEditTime: 2024-09-21 23:10:57
 * @Description: 百度小程序接口类
 * Copyright 2024 运城市盘石网络科技有限公司
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
                "type" => "GET",
                "url"  => "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id={$this->CFG['appid']}&client_secret={$this->CFG['appsecret']}&scope=smartapp_snsapi_base",
            ]);
            $token = json_decode($token, true);
            if ($token['access_token']) {
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
     * @description: 获取小程序码
     * @param string $path
     * @return {*}
     */
    public function get_qrcode($path)
    {
        $this->access_token();
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => "https://openapi.baidu.com/rest/2.0/smartapp/qrcode/getunlimitedv2?access_token={$this->CFG['access_token']['access_token']}",
            "data"    => [
                "path" => $path,
            ],
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded",
            ],
        ]), true);
        $result = json_decode($result, true);
        return $result ?: [];
    }
}
