<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 手机浏览器H5支付
class WxPayCheck
{
    public function Check($config, $order)
    {
        global $_L;
        $url   = "https://api.mch.weixin.qq.com/pay/orderquery";
        $input = [
            "appid"        => $config->$get['appid'],
            "mch_id"       => $config->$get['mch_id'],
            "out_trade_no" => $order['order_no'],
            "sign_type"    => $config->$get['sign_type'],
            "nonce_str"    => WxPayApi::NonceStr(),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, false, 6));
        return $result;
    }
}
