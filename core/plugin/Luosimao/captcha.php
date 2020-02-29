<?php
class CAPTCHA
{
    public $cfg = array();
    public function __construct($config)
    {
        $this->$cfg = $config;
    }
    /**
     * [get 获取html]
     * @return [type] [description]
     */
    public function get($callback = "")
    {
        $callback = $callback ? ' data-callback="' . $callback . '"' : '';
        $html     = '<div class="l-captcha" data-width="100%"' . $callback . ' data-site-key="' . $this->$cfg['site_key'] . '"></div><script type="text/javascript" src="//captcha.luosimao.com/static/dist/api.js" async defer></script>';
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
            "api_key"  => $this->$cfg['api_key'],
            "response" => $response,
        ], true), true);
        if ($result['error'] == "0" && $result['res'] == "success") {
            return true;
        } else {
            return false;
        }
    }
}
