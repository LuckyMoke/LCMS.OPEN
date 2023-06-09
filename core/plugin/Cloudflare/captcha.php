<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-02 16:08:15
 * @LastEditTime: 2023-06-03 18:20:36
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
        $html = '<style>.cf-turnstile{position:relative;height:65px}.cf-turnstile:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:65px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}.cf-turnstile iframe{width:100%!important;position:relative;z-index:1}</style><div class="cf-turnstile" data-sitekey="' . $YZCFG['site_key'] . '"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script><script>var CAPTCHAONSUBMIT=function(query,callback){query=query.replace(/cf-turnstile-response/,"response_token");callback(true,query)},CAPTCHARESET=function(){turnstile.reset()};</script>';
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
        $result = json_decode(HTTP::post("https://challenges.cloudflare.com/turnstile/v0/siteverify", [
            "secret"   => $YZCFG['secret'],
            "response" => $form['response_token'],
            "remoteip" => CLIENT_IP,
        ]), true);
        if ($result['success'] == true) {
            return true;
        } else {
            return false;
        }
    }
}
