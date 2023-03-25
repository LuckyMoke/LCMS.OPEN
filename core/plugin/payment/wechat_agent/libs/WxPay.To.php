<?php
class WxPayTo
{
    public $api, $cfg, $order;
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
    }
    /**
     * @description: 商家转账到零钱
     * @param {*}
     * @return {*}
     */
    public function Pay()
    {
        $url    = "/v3/transfer/batches";
        $result = $this->postJson($url, json_encode([
            "appid"                => $this->cfg['appid'],
            "out_batch_no"         => $this->order['order_no'],
            "batch_name"           => $this->order['batch_name'],
            "batch_remark"         => $this->order['batch_remark'],
            "total_amount"         => $this->order['pay'] * 100,
            "total_num"            => 1,
            "transfer_detail_list" => [[
                "out_detail_no"   => $this->order['order_no'],
                "transfer_amount" => $this->order['pay'] * 100,
                "transfer_remark" => $this->order['batch_remark'],
                "openid"          => $this->order['openid'],
            ]],
        ]));
        if ($result['code'] || !$result['batch_id']) {
            LCMS::X(401, $result['message']);
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
            "Authorization" => "WECHATPAY2-SHA256-RSA2048 " . WxPayApi::Sign($this->cfg, [
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
}
