<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 退款
class WxPayRepay
{
    public function Order($config, $order)
    {
        $url   = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $input = [
            "appid"         => $config->get['appid'],
            "mch_id"        => $config->get['mch_id'],
            "sub_appid"     => $config->get['sub_appid'],
            "sub_mch_id"    => $config->get['sub_mch_id'],
            "out_trade_no"  => $order['order_no'],
            "out_refund_no" => "{$order['order_no']}R",
            "total_fee"     => $order['pay'] * 100,
            "refund_fee"    => $order['pay'] * 100,
            "sign_type"     => $config->get['sign_type'],
            "nonce_str"     => WxPayApi::NonceStr(),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, true));
        return $result;
    }
}
