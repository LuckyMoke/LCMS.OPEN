<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 手机浏览器H5支付
class WxPayH5
{
    public function Order($config, $order)
    {
        global $_L;
        $url   = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $input = [
            "appid"            => $config->$get['appid'],
            "mch_id"           => $config->$get['mch_id'],
            "sub_appid"        => $config->$get['sub_appid'],
            "sub_mch_id"       => $config->$get['sub_mch_id'],
            "body"             => $order['body'],
            "out_trade_no"     => $order['order_no'],
            "total_fee"        => $order['pay'] * 100,
            "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
            "notify_url"       => $config->$get['notify_url'],
            "trade_type"       => "MWEB",
            "sign_type"        => $config->$get['sign_type'],
            "nonce_str"        => WxPayApi::NonceStr(),
            "scene_info"       => json_encode([
                "h5_info" => [
                    "type"     => "Wap",
                    "wap_url"  => $_L['url']['now'],
                    "wap_name" => $order['body'],
                ],
            ]),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, false, 6));
        return $result;
    }
}