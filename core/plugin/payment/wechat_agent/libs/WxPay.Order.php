<?php
class WxPayOrder
{
    public $api, $cfg, $order, $agent;
    /**
     * @description: 接口初始化
     * @param array $init
     * @return {*}
     */
    public function __construct($init)
    {
        $this->api   = "https://api.mch.weixin.qq.com";
        $this->cfg   = $init['config'];
        $this->order = $init['order'];
        $this->agent = $init['config']['agent'];
    }
    /**
     * @description: Jsapi下单
     * @param string $openid
     * @return array
     */
    public function Jsapi($openid = "")
    {
        $url    = "/v3/pay/partner/transactions/jsapi";
        $openid = $openid ?: $this->getOpenid();
        $openid || LCMS::X(403, "缺少OPENID");
        $result = $this->postJson($url, json_encode([
            "sp_appid"     => $this->agent['appid'],
            "sp_mchid"     => $this->agent['mch_id'],
            "sub_appid"    => $this->cfg['appid'],
            "sub_mchid"    => $this->cfg['mch_id'],
            "description"  => $this->order['body'],
            "out_trade_no" => $this->order['order_no'],
            "notify_url"   => $this->cfg['notify_url'],
            "amount"       => [
                "total"    => intval($this->order['pay'] * 100),
                "currency" => "CNY",
            ],
            "payer"        => [
                $this->cfg['appsecret'] ? "sub_openid" : "sp_openid" => $openid,
            ],
        ]));
        if ($result['code'] || !$result['prepay_id']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            $jsapi = [
                "appId"     => $this->agent["appid"],
                "timeStamp" => strval(time()),
                "nonceStr"  => randstr(32, "let"),
                "package"   => "prepay_id={$result['prepay_id']}",
            ];
            return array_merge($jsapi, [
                "paySign"  => WxPayApi::Sign($this->agent, $jsapi),
                "signType" => $this->cfg['sign_type'],
            ]);
        }
    }
    /**
     * @description: H5下单
     * @param {*}
     * @return array
     */
    public function H5()
    {
        $url    = "/v3/pay/partner/transactions/h5";
        $result = $this->postJson($url, json_encode([
            "sp_appid"     => $this->agent['appid'],
            "sp_mchid"     => $this->agent['mch_id'],
            "sub_appid"    => $this->cfg['appid'],
            "sub_mchid"    => $this->cfg['mch_id'],
            "description"  => $this->order['body'],
            "out_trade_no" => $this->order['order_no'],
            "notify_url"   => $this->cfg['notify_url'],
            "amount"       => [
                "total"    => intval($this->order['pay'] * 100),
                "currency" => "CNY",
            ],
            "scene_info"   => [
                "payer_client_ip" => CLIENT_IP,
                "h5_info"         => [
                    "type" => "WAP",
                ],
            ],
        ]));
        if ($result['code'] || !$result['h5_url']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
    /**
     * @description: 电脑端下单
     * @param {*}
     * @return array
     */
    public function Pc()
    {
        $url    = "/v3/pay/partner/transactions/native";
        $result = $this->postJson($url, json_encode([
            "sp_appid"     => $this->agent['appid'],
            "sp_mchid"     => $this->agent['mch_id'],
            "sub_appid"    => $this->cfg['appid'],
            "sub_mchid"    => $this->cfg['mch_id'],
            "description"  => $this->order['body'],
            "out_trade_no" => $this->order['order_no'],
            "notify_url"   => $this->cfg['notify_url'],
            "amount"       => [
                "total"    => intval($this->order['pay'] * 100),
                "currency" => "CNY",
            ],
        ]));
        if ($result['code'] || !$result['code_url']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
    /**
     * @description: 退款操作
     * @param {*}
     * @return array
     */
    public function Repay()
    {
        $url    = "/v3/refund/domestic/refunds";
        $result = $this->postJson($url, json_encode([
            "out_trade_no"  => $this->order['order_no'],
            "out_refund_no" => $this->order['order_no'] . "R",
            "amount"        => [
                "refund"   => intval($this->order['pay'] * 100),
                "total"    => intval($this->order['pay'] * 100),
                "currency" => "CNY",
            ],
        ]));
        if ($result['code']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
    /**
     * @description: 订单状态监测
     * @param {*}
     * @return array
     */
    public function Check()
    {
        global $_L;
        $url    = $this->api . "/v3/pay/transactions/out-trade-no/{$this->order['order_no']}?sp_mchid=" . $this->agent['mch_id'] . "&sub_mchid=" . $this->cfg['mch_id'];
        $result = json_decode(WxPayApi::Request("GET", $url, "", [
            "Authorization" => WxPayApi::Sign($this->agent, [
                "method"    => "GET",
                "url"       => $url,
                "timeStamp" => time(),
                "nonceStr"  => randstr(32, "let"),
                "body"      => "",
            ]),
        ]), true);
        if ($result['code']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
    /**
     * @description: 提交JSON数据
     * @param string $url
     * @param string $body
     * @return array
     */
    private function postJson($url, $body)
    {
        $url    = $this->api . $url;
        $result = json_decode(WxPayApi::Request("POST", $url, $body, [
            "Authorization" => WxPayApi::Sign($this->agent, [
                "method"    => "POST",
                "url"       => $url,
                "timeStamp" => time(),
                "nonceStr"  => randstr(32, "let"),
                "body"      => $body,
            ]),
            "Content-Type"  => "application/json; charset=utf-8",
            "Accept"        => "application/json",
        ]), true);
        return $result ?: [];
    }
    /**
     * @description: 获取用户OPENID
     * @return string
     */
    private function getOpenid()
    {
        global $_L;
        $cfg = $this->cfg['appid'] ? $this->cfg : $this->agent;
        load::plugin('WeChat/OA');
        $WX = new OA([
            "appid"     => $cfg['appid'],
            "appsecret" => $cfg['appsecret'],
        ]);
        $sname = $WX->SID . "snsapi_base";
        $user  = SESSION::get($sname);
        if ($user['openid']) {
            return $user['openid'];
        } else {
            if ($cfg['thirdapi']) {
                if (in_string($_L['form']['code'], "OPENID|")) {
                    $code = str_replace("OPENID|", "", $_L['form']['code']);
                    $code = json_decode(ssl_decode($code), true);
                    if ($code['time'] > time()) {
                        SESSION::set($sname, [
                            "openid" => $code['openid'],
                        ]);
                        return $code['openid'];
                    }
                }
                okinfo($cfg['thirdapi'] . "oauth&scope=snsapi_base&goback=" . urlencode($_L['url']['now']));
            } else {
                if (!isset($_L['form']['code'])) {
                    $query = http_build_query([
                        "appid"         => $cfg['appid'],
                        "redirect_uri"  => $_L['url']['now'],
                        "response_type" => "code",
                        "scope"         => "snsapi_base",
                    ]);
                    $WX->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
                    exit();
                } else {
                    $user = $WX->getOpenidFromMp($_L['form']['code']);
                    if ($user['openid']) {
                        SESSION::set($sname, $user);
                        return $user['openid'];
                    }
                }
            }
        }
    }
}
