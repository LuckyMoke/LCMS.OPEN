<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2025-02-19 11:08:33
 * @LastEditTime: 2025-02-19 12:02:36
 * @Description: 支付宝小程序接口
 * Copyright 2025 运城市盘石网络科技有限公司
 */
require "libs/Alipay.Api.php";
class MP
{
    public $CFG;
    public $API;
    public function __construct($config)
    {
        $this->CFG = [
            "appid"      => $config['appid'],
            "privatekey" => $config['privatekey'],
            "publickey"  => $config['publickey'],
        ];
        $this->API = new AlipayApi($this->CFG);
    }
    /**
     * @description: 使用code进行oauth
     * @param string $code
     * @return array
     */
    public function oauth($code)
    {
        $input = [
            "app_id"     => $this->CFG['appid'],
            "method"     => "alipay.system.oauth.token",
            "format"     => "JSON",
            "charset"    => "utf-8",
            "sign_type"  => "RSA2",
            "timestamp"  => datenow(),
            "version"    => "1.0",
            "grant_type" => "authorization_code",
            "code"       => $code,
        ];
        $result = HTTP::request([
            "type" => "GET",
            "url"  => "https://openapi.alipay.com/gateway.do",
            "data" => $this->API->sign($input),
        ]);
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 获取用户令牌
     * @param {*} $code
     * @return {*}
     */
    public function access_token($code)
    {
        $result = $this->oauth($code);
        if (
            $result['alipay_system_oauth_token_response'] &&
            $result['alipay_system_oauth_token_response']['access_token']
        ) {
            return $result['alipay_system_oauth_token_response'];
        }
        return $result['error_response'] ?? [];
    }
    /**
     * @description: 获取用户openid
     * @param string $code
     * @return string
     */
    public function openid($code)
    {
        $result = $this->oauth($code);
        if (
            $result['alipay_system_oauth_token_response'] &&
            $result['alipay_system_oauth_token_response']['open_id']
        ) {
            return $result['alipay_system_oauth_token_response'];
        }
        return $result['error_response'] ?? [];
    }
    /**
     * @description: 获取小程序码
     * @param string $biz_content
     * @return {*}
     */
    public function get_qrcode($biz_content = [])
    {
        $input = [
            "app_id"      => $this->CFG['appid'],
            "method"      => "alipay.open.app.qrcode.create",
            "format"      => "JSON",
            "charset"     => "utf-8",
            "sign_type"   => "RSA2",
            "timestamp"   => datenow(),
            "version"     => "1.0",
            "biz_content" => json_encode($biz_content),
        ];
        $result = HTTP::request([
            "type"    => "POST",
            "url"     => "https://openapi.alipay.com/gateway.do",
            "data"    => $this->API->sign($input),
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            ],
        ]);
        $result = json_decode($result, true);
        if (
            $result['alipay_open_app_qrcode_create_response'] &&
            $result['alipay_open_app_qrcode_create_response']['qr_code_url']
        ) {
            return $result['alipay_open_app_qrcode_create_response'];
        }
        return $result['alipay_open_app_qrcode_create_response'] ?? [];
    }
}
