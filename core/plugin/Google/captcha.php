<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-08-19 17:26:04
 * @LastEditTime: 2023-06-03 18:17:13
 * @Description: 谷歌reCAPTCHA v3
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
        $html = '<script src="https://www.recaptcha.net/recaptcha/api.js?render=' . $YZCFG['site_key'] . '" defer></script><script>var CAPTCHAONSUBMIT=function(query,callback){grecaptcha.ready(function(){grecaptcha.execute("' . $YZCFG['site_key'] . '",{action:"login"}).then(function(token){callback(true,query+"&response_token="+encodeURIComponent(token))})})};</script>';
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
        $result = json_decode(HTTP::post("https://www.recaptcha.net/recaptcha/api/siteverify", [
            "secret"   => $YZCFG['secret'],
            "response" => $form['response_token'],
            "remoteip" => CLIENT_IP,
        ]), true);
        if ($result['success'] == true && $result['score'] > 0.5) {
            return true;
        } else {
            return false;
        }
    }
}
