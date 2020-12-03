<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-03 13:09:34
 * @Description:微信小程序接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class MP
{
    public $cfg;
    public function __construct($config)
    {
        $this->cfg = [
            "appid"     => $config['appid'],
            "appsecret" => $config['appsecret'],
        ];
        $this->cache();
    }
    /**
     * [cache 数据缓存读取与保存]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function cache($type = "get")
    {
        if ($this->cfg['appid'] && $this->cfg['appsecret']) {
            $cname = md5($this->cfg['appid'] . $this->cfg['appsecret']);
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cache([
                    "name" => $cname,
                    "data" => $this->cfg,
                ]);
                break;
            default:
                $arr = LCMS::cache([
                    "name" => $cname,
                ]);
                if (is_array($arr)) {
                    $this->cfg = array_merge($arr, $this->cfg);
                }
                break;
        }
    }
    /**
     * [openid 通过登陆code获取用户OPENID]
     * @param  [type] $js_code [description]
     * @return [type]          [description]
     */
    public function openid($js_code)
    {
        $query = http_build_query([
            "appId"      => $this->cfg['appid'],
            "secret"     => $this->cfg['appsecret'],
            "js_code"    => $js_code,
            "grant_type" => "authorization_code",
        ]);
        $result = json_decode(http::get("https://api.weixin.qq.com/sns/jscode2session?{$query}"), true);
        return $result;
    }
    /**
     * [access_token 生成access_token缓存]
     * @return [type] [description]
     */
    public function access_token()
    {
        if (!$this->cfg['access_token']['token'] || $this->cfg['access_token']['expires'] < time()) {
            $query = http_build_query(array(
                "appid"      => $this->cfg['appid'],
                "secret"     => $this->cfg['appsecret'],
                "grant_type" => "client_credential",
            ));
            $token = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/token?{$query}"), true);
            if ($token['errcode']) {
                return $token;
            } else {
                $this->cfg['access_token'] = [
                    "token"   => $token['access_token'],
                    "expires" => time() + 3600,
                ];
                $this->cache("save");
            }
        }
    }
    /**
     * [qrcode 获取小程序码]
     * @param  [type] $path    [description]
     * @param  string $scene   [description]
     * @return [type]          [description]
     */
    public function qrcode($path, $scene = "")
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$this->cfg['access_token']['token']}", json_encode_ex([
            "scene" => $scene,
            "path"  => $path,
        ]));
        if ($result && is_array(json_decode($result, true))) {
            $result = json_decode($result, true);
        }
        return $result;
    }
    /**
     * [send_tpl 发送小程序模板消息]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function send_tpl($para = [])
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$this->cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [send_unitpl 发送统一服务消息]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function send_unitpl($para = [])
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$this->cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [encode_data 解密敏感数据]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function encode_data($para = [])
    {
        if ($para['iv'] && $para['encryptedData'] && $para['session_key'] && $para['openid']) {
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
