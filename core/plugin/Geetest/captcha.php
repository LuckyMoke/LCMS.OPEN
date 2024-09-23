<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-04-25 14:38:33
 * @LastEditTime: 2024-09-21 23:14:59
 * @Description: 极验行为验
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
        $html = '<style>.l-captcha{position:relative;height:36px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:36px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}</style><div class="l-captcha-input"></div><div class="l-captcha"></div><script type="text/javascript" src="https://static.geetest.com/v4/gt4.js"></script><script>var captchaObj;initGeetest4({captchaId:"' . $YZCFG['site_key'] . '",nativeButton:{width:"100%",height:"100%"},},function(obj){captchaObj=obj;obj.appendTo(".l-captcha");obj.onSuccess(function(){var result=obj.getValidate();$(".l-captcha-input").append(\'<textarea name="response_token" style="position:absolute;opacity:0;width:0;height:0">\'+JSON.stringify(result)+"</textarea>")})});var CAPTCHARESET=function(){captchaObj.reset();$(".l-captcha-input").html("")};</script>';
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
        $post       = json_decode($form['response_token'], true);
        $sign_token = hash_hmac('sha256', $post['lot_number'], $YZCFG['secret']);
        $result     = json_decode(HTTP::request([
            "type"  => "POST",
            "url"   => "http://gcaptcha4.geetest.com/validate",
            "data"  => [
                "captcha_id"     => $YZCFG['site_key'],
                "lot_number"     => $post['lot_number'],
                "captcha_output" => $post['captcha_output'],
                "lot_number"     => $post['lot_number'],
                "pass_token"     => $post['pass_token'],
                "gen_time"       => $post['gen_time'],
                "sign_token"     => $sign_token,
            ],
            "build" => true,
        ]), true);
        if ($result['result'] === "success") {
            return true;
        } else {
            return false;
        }
    }
}
