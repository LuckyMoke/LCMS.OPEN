<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 企业付款到零钱
class WxPayTo
{
    public function Order($config, $order)
    {
        $url   = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $input = [
            "mch_appid"        => $config->$get['appid'],
            "mchid"            => $config->$get['mch_id'],
            "partner_trade_no" => $order['order_no'],
            "openid"           => $order['openid'],
            "check_name"       => "NO_CHECK",
            "amount"           => $order['pay'] * 100,
            "desc"             => $order['desc'],
            "spbill_create_ip" => SERVER_IP,
            "nonce_str"        => WxPayApi::NonceStr(),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, true));
        return $result;
    }
}
