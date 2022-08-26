<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-08-19 17:26:04
 * @LastEditTime: 2022-08-19 19:29:03
 * @Description: 谷歌reCAPTCHA v3
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
        $html = '<script src="https://www.recaptcha.net/recaptcha/api.js?render=' . $this->cfg['site_key'] . '" defer></script><script type="text/javascript">var CAPTCHAONSUBMIT=function(query,callback){grecaptcha.ready(function(){grecaptcha.execute(\'' . $this->cfg['site_key'] . '\', {action: \'login\'}).then(function(token){callback(true,query + \'&recaptcha_token=\'+encodeURIComponent(token));});});};</script>';
        return $html;
    }
    /**
     * @description: 检查验证码
     * @param string $token
     * @return bool
     */
    public function check($token = "")
    {
        $result = json_decode(HTTP::post("https://www.recaptcha.net/recaptcha/api/siteverify", [
            "secret"   => $this->cfg['secret'],
            "response" => $token,
            "remoteip" => CLIENT_IP,
        ]), true);
        if ($result['success'] == true) {
            return $result['score'];
        } else {
            return false;
        }
    }
}
