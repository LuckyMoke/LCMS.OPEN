<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-04-25 14:38:33
 * @LastEditTime: 2024-09-21 23:23:21
 * @Description: Vaptcha验证码
 * Copyright 2022 运城市盘石网络科技有限公司
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
        $html = '<style>.l-captcha{position:relative;height:36px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:36px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}</style><div class="l-captcha-input"></div><div class="l-captcha"></div><script type="text/javascript" src="https://v-cn.vaptcha.com/v3.js"></script><script>var VAPTCHAObj;vaptcha({vid:"' . $YZCFG['site_key'] . '",mode:"click",scene:0,container:".l-captcha",area:"auto",}).then(function(obj){VAPTCHAObj=obj;obj.render();obj.renderTokenInput(".l-captcha-input")});var CAPTCHAONSUBMIT=function(query,callback){query=query.replace(/vaptcha_token/,"response_token");callback(true,query)},CAPTCHARESET=function(){VAPTCHAObj.reset();$(".l-captcha-input").html("")};</script>';
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
        if (!in_string($form['vaptcha_server'], [
            "vaptcha.com/verify", "vaptcha.net/verify",
        ])) {
            return false;
        }
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => $form['vaptcha_server'],
            "data"    => json_encode([
                "id"        => $YZCFG['site_key'],
                "secretkey" => $YZCFG['secret'],
                "scene"     => 0,
                "token"     => $form['response_token'],
                "ip"        => CLIENT_IP,
            ]),
            "headers" => [
                "Content-Type" => "application/json",
            ],
        ]), true);
        if ($result['success'] === 1 && $result['score'] > 50) {
            return true;
        } else {
            return false;
        }
    }
}
