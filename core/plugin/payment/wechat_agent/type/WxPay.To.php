<?php
load::plugin("payment/wechat_agent/type/lib/WxPay.Api");
class WxPayTo
{
    public function Order($config, $order)
    {
        $url   = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $input = [
            "mch_id"       => $config->$get['mch_id'],
            "sub_mch_id"   => $config->$get['sub_mch_id'],
            "wxappid"      => $config->$get['appid'],
            "msgappid"     => $config->$get['sub_appid'],
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