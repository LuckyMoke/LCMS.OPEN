<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-04-25 14:38:33
 * @LastEditTime: 2022-04-25 16:23:27
 * @Description: Vaptcha验证码
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
        $html = '<style>.l-captcha{position:relative;height:36px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:36px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}</style><div class="l-captcha-input"></div><div class="l-captcha"></div><script type="text/javascript" src="https://v-cn.vaptcha.com/v3.js"></script><script>var CAPTCHARESET;vaptcha({vid:"' . $this->cfg['vid'] . '",mode:"click",scene:0,container:".l-captcha",area:"auto",}).then(function(obj){CAPTCHARESET=function(){obj.reset();$(".l-captcha-input").html("")};obj.render();obj.renderTokenInput(".l-captcha-input")});</script>';
        return $html;
    }
    /**
     * @description: 检查验证码
     * @param string $url
     * @param string $token
     * @return bool
     */
    public function check($url = "", $token = "")
    {
        if (!in_string($url, [
            "vaptcha.com/verify", "vaptcha.net/verify",
        ])) {
            return false;
        }
        $result = json_decode(HTTP::post($url, [
            "id"        => $this->cfg['vid'],
            "secretkey" => $this->cfg['key'],
            "scene"     => 0,
            "token"     => $token,
            "ip"        => CLIENT_IP,
        ], false, [
            "Content-Type" => "application/json",
        ]), true);
        if ($result['success'] === 1) {
            return true;
        } else {
            return false;
        }
    }
}
