<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 发送红包给用户
class WxPayTo
{
    public function Order($config, $order)
    {
        $url   = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $input = [
            "mch_id"       => $config->get['mch_id'],
            "wxappid"      => $config->get['appid'],
            "mch_billno"   => $order['order_no'],
            "send_name"    => $order['send_name'],
            "re_openid"    => $order['openid'],
            "total_amount" => $order['pay'] * 100,
            "total_num"    => 1,
            "wishing"      => $order['wishing'],
            "client_ip"    => SERVER_IP,
            "act_name"     => $order['act_name'],
            "remark"       => $order['remark'],
            "scene_id"     => $order['pay'] < "1.00" || $order['pay'] > "200.00" ? "PRODUCT_1" : "",
            "nonce_str"    => WxPayApi::NonceStr(),
        ];
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, true));
        return $result;
    }
}
