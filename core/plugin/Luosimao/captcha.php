<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-09-21 23:13:19
 * @Description: Luosimao验证码
 * @Copyright 2020 运城市盘石网络科技有限公司
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
     * @return string
     */
    public function get()
    {
        global $YZCFG;
        $html = '<style>.l-captcha{position:relative;height:44px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:44px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}.l-captcha iframe{position:relative;z-index:1}</style><div class="l-captcha" data-width="100%" data-site-key="' . $YZCFG['site_key'] . '"></div><script type="text/javascript" src="https://captcha.luosimao.com/static/dist/api.js" defer></script><script>var CAPTCHAONSUBMIT=function(query,callback){query=query.replace(/luotest_response/,"response_token");callback(true,query)},CAPTCHARESET=function(){LUOCAPTCHA.reset()};</script>';
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
            "type"  => "POST",
            "url"   => "https://captcha.luosimao.com/api/site_verify",
            "data"  => [
                "api_key"  => $YZCFG['secret'],
                "response" => $form['response_token'],
            ],
            "build" => true,
        ]), true);
        if ($result['error'] == "0" && $result['res'] === "success") {
            return true;
        } else {
            return false;
        }
    }
}
