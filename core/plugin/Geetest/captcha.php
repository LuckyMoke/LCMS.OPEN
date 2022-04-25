<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-04-25 14:38:33
 * @LastEditTime: 2022-04-25 15:21:12
 * @Description: 极验行为验
 * Copyright 2022 运城市盘石网络科技有限公司
 */
class CAPTCHA
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
    }
    /**
     * @description: 获取html
     * @param {*}
     * @return string
     */
    public function get()
    {
        $html = '<style>.l-captcha{position:relative;height:36px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:36px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}</style><div class="l-captcha-input"></div><div class="l-captcha"></div><script type="text/javascript" src="https://static.geetest.com/v4/gt4.js"></script><script>var CAPTCHARESET;initGeetest4({captchaId:"' . $this->cfg['captcha_id'] . '",nativeButton:{width:"100%",height:"100%"}},function(captcha){CAPTCHARESET=function(){captcha.reset();$(".l-captcha-input").html("")};captcha.appendTo(".l-captcha");captcha.onSuccess(function(){var result=captcha.getValidate();for(const key in result){$(".l-captcha-input").append(\'<input name="GEETEST[\' + key + \']"value="\' + result[key] + \'"type="hidden"/>\')}})});</script>';
        return $html;
    }
    /**
     * @description: 检查验证码
     * @param array $POST
     * @return bool
     */
    public function check($POST = [])
    {
        $url = "http://gcaptcha4.geetest.com/validate";
        //生成签名
        $sign_token = hash_hmac('sha256', $POST['lot_number'], $this->cfg['captcha_key']);
        $result     = json_decode(http::post($url, [
            "captcha_id"     => $this->cfg['captcha_id'],
            "lot_number"     => $POST['lot_number'],
            "captcha_output" => $POST['captcha_output'],
            "lot_number"     => $POST['lot_number'],
            "pass_token"     => $POST['pass_token'],
            "gen_time"       => $POST['gen_time'],
            "sign_token"     => $sign_token,
        ], true), true);
        if ($result['result'] === "success") {
            return true;
        } else {
            return false;
        }
    }
}
