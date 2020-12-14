<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-13 15:31:55
 * @Description:Luosimao验证码
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class CAPTCHA
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
    }
    /**
     * [get 获取html]
     * @return [type] [description]
     */
    public function get($callback = "")
    {
        $callback = $callback ? ' data-callback="' . $callback . '"' : '';
        $html     = '<style>.l-captcha{position:relative;height:44px}.l-captcha:before{content:"\4eba\673a\9a8c\8bc1\542f\52a8\4e2d\002e\002e\002e";line-height:44px;text-align:center;color:rgba(0,0,0,.3);display:block;background:#f1f1f1;position:absolute;top:0;left:0;width:100%;z-index:0}.l-captcha iframe{position:relative;z-index:1}</style><div class="l-captcha" data-width="100%"' . $callback . ' data-site-key="' . $this->cfg['site_key'] . '"></div><script type="text/javascript" src="https://captcha.luosimao.com/static/dist/api.js" async defer></script>';
        return $html;
    }
    /**
     * [check 检查验证码]
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public function check($response)
    {
        $url    = "https://captcha.luosimao.com/api/site_verify";
        $result = json_decode(http::post($url, [
            "api_key"  => $this->cfg['api_key'],
            "response" => $response,
        ], true), true);
        if ($result['error'] == "0" && $result['res'] == "success") {
            return true;
        } else {
            return false;
        }
    }
}
