<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-02 16:08:15
 * @LastEditTime: 2024-09-21 23:12:00
 * @Description: Turnstile验证
 * Copyright 2023 运城市盘石网络科技有限公司
 */
class CAPTCHA
{
    public function __construct($config)
    {
        global $YZCFG;
        $YZCFG = $config;
    }
    /**
     * @description: 获取html
     * @param {*}
     * @return string
     */
    public function get()
    {
        global $YZCFG;
        $html = '<style>.lcms-cf-turnstile{position:relative;height:65px}.lcms-cf-turnstile:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:65px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}.lcms-cf-turnstile iframe{width:100%!important;position:relative;z-index:1}</style><div class="lcms-cf-turnstile"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback" defer></script><script>var CAPTCHARESET=function(){turnstile.reset()};window.onloadTurnstileCallback=function(){turnstile.render(".lcms-cf-turnstile",{sitekey: "' . $YZCFG['site_key'] . '",callback:function(token){if(typeof CAPTCHACALLBACK=="function"){CAPTCHACALLBACK(token)}}})};</script>';
        return $html;
    }
    /**
     * @description: 检查验证码
     * @param array $form
     * @return bool
     */
    public function check($form)
    {
        global $YZCFG;
        $result = json_decode(HTTP::request([
            "type" => "POST",
            "url"  => "https://challenges.cloudflare.com/turnstile/v0/siteverify",
            "data" => [
                "secret"   => $YZCFG['secret'],
                "response" => $form['response_token'],
                "remoteip" => CLIENT_IP,
            ],
        ]), true);
        if ($result['success'] == true) {
            return true;
        } else {
            return false;
        }
    }
}
