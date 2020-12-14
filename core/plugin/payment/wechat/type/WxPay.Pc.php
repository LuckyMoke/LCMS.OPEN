<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 电脑扫描支付
class WxPayPc
{
    public function Order($config, $order)
    {
        $url   = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $input = [
            "appid"            => $config->get['appid'],
            "mch_id"           => $config->get['mch_id'],
            "body"             => $order['body'],
            "out_trade_no"     => $order['order_no'],
            "total_fee"        => $order['pay'] * 100,
            "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
            "notify_url"       => $config->get['notify_url'],
            "trade_type"       => "NATIVE",
            "openid"           => $openid['openid'],
            "sign_type"        => $config->get['sign_type'],
            "nonce_str"        => WxPayApi::NonceStr(),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, false, 6));
        return $result;
    }
}
