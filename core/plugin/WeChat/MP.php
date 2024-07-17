<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-07-10 14:31:58
 * @Description:微信小程序接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class MP
{
    public $CFG;
    public function __construct($config)
    {
        $this->CFG = [
            "appid"     => $config['appid'],
            "appsecret" => $config['appsecret'],
        ];
        $this->cache();
    }
    /**
     * @description: 数据缓存读取与保存
     * @param string $type
     * @return {*}
     */
    public function cache($type = "get")
    {
        if ($this->CFG['appid'] && $this->CFG['appsecret']) {
            $cname = $this->CFG['appid'] . $this->CFG['appsecret'];
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cache($cname, $this->CFG);
                break;
            case 'clear':
                LCMS::cache($cname, "clear");
                break;
            default:
                $arr = LCMS::cache($cname);
                if (is_array($arr)) {
                    $this->CFG = array_merge($arr, $this->CFG);
                }
                break;
        }
    }
    /**
     * @description: 获取全局access_token
     * @return {*}
     */
    public function access_token()
    {
        if (!$this->CFG['access_token'] || $this->CFG['access_token']['expires_in'] < time()) {
            $query = http_build_query(array(
                "appid"      => $this->CFG['appid'],
                "secret"     => $this->CFG['appsecret'],
                "grant_type" => "client_credential",
            ));
            $token = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/token?{$query}"), true);
            if ($token['access_token']) {
                $this->CFG['access_token'] = [
                    "access_token" => $token['access_token'],
                    "expires_in"   => time() + 3600,
                ];
                $this->cache("save");
            } else {
                return $token;
            }
        }
        return $this->CFG['access_token'] ?: [];
    }
    /**
     * @description: 通过登陆code获取用户OPENID
     * @param string $code
     * @return {*}
     */
    public function openid($code)
    {
        $query = http_build_query([
            "appId"      => $this->CFG['appid'],
            "secret"     => $this->CFG['appsecret'],
            "js_code"    => $code,
            "grant_type" => "authorization_code",
        ]);
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/sns/jscode2session?{$query}"), true);
        return $result ?: [];
    }
    /**
     * @description: 获取手机号
     * @param string $code
     * @return array
     */
    public function get_phone($code)
    {
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$this->CFG['access_token']['access_token']}", json_encode([
            "code" => $code,
        ]));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 获取小程序码
     * @param string $page 页面链接 pages/index/index
     * @param string $scene 页面参数 a=1
     * @return {*}
     */
    public function get_qrcode($page, $scene = "", $env = "release")
    {
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$this->CFG['access_token']['access_token']}", json_encode([
            "scene"       => $scene ?: "&",
            "page"        => $page,
            "env_version" => $env,
        ]));
        if (in_string(HTTP::$HEADERS['Content-Type'], "application/json")) {
            $result = json_decode($result, true);
            return $result ?: [];
        } else {
            return $result;
        }
    }
    /**
     * @description: 获取小程序链接
     * @param string $path
     * @param string $query
     * @return {*}
     */
    public function get_urllink($path, $query = "", $env = "release")
    {
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/wxa/generate_urllink?access_token={$this->CFG['access_token']['access_token']}", json_encode([
            "path"        => $path,
            "query"       => $query,
            "env_version" => $env,
        ]));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * [send_unitpl 发送统一服务消息]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function send_unitpl($para = [])
    {
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$this->CFG['access_token']['access_token']}", json_encode($para));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 发送订阅消息
     * @param array $para
     * @return array
     */
    public function send_subscribe($para = [])
    {
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$this->CFG['access_token']['access_token']}", json_encode($para));
        return json_decode($result, true);
    }
    /**
     * [encode_data 解密敏感数据]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function encode_data($para = [])
    {
        if ($para['iv'] && $para['encryptedData'] && $para['session_key']) {
            if (strlen($para['session_key']) == 24 && strlen($para['iv']) == 24) {
                $result = openssl_decrypt(base64_decode($para['encryptedData']), "AES-128-CBC", base64_decode($para['session_key']), 1, base64_decode($para['iv']));
                $result = json_decode($result, true);
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
