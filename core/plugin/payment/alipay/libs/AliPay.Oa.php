<?php
class AliPayOa
{
    public $api, $cfg, $order;
    /**
     * @description: 接口初始化
     * @param array $init
     * @return {*}
     */
    public function __construct($init)
    {
        $this->api   = $init['config']['gatewayurl'];
        $this->cfg   = $init['config'];
        $this->order = $init['order'];
    }
    /**
     * @description: 获取用户UID
     * @return array
     */
    public function getUid()
    {
        $input = [
            'method'     => 'alipay.system.oauth.token',
            'app_id'     => $this->cfg['appid'],
            'format'     => $this->cfg['format'],
            'charset'    => $this->cfg['charset'],
            'sign_type'  => $this->cfg['sign_type'],
            'timestamp'  => $this->cfg['timestamp'],
            'version'    => $this->cfg['version'],
            "grant_type" => "authorization_code",
            "code"       => $this->order['auth_code'],
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => $this->api,
            "data"    => $input,
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            ],
        ]), true);
        if ($result && $result['alipay_system_oauth_token_response']) {
            return $result['alipay_system_oauth_token_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
}
