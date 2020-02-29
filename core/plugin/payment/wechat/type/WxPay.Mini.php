<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// 小程序支付
class WxPayMini
{
    public function Order($config, $order)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        if ($order['openid']) {
            $input = [
                "appid"            => $config->$get['appid'],
                "mch_id"           => $config->$get['mch_id'],
                "body"             => $order['body'],
                "out_trade_no"     => $order['order_no'],
                "total_fee"        => $order['pay'] * 100,
                "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
                "notify_url"       => $config->$get['notify_url'],
                "trade_type"       => "JSAPI",
                "openid"           => $order['openid'],
                "sign_type"        => $config->$get['sign_type'],
                "nonce_str"        => WxPayApi::NonceStr(),
            ];
        }
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = $this->GetJsApiParameters($config, WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, false, 6)));
        return $result;
    }
    /**
     * [GetJsApiParameters JSAPI支付参数生成]
     * @param [type] $config [description]
     * @param [type] $result [description]
     */
    public function GetJsApiParameters($config, $result)
    {
        if ($result['return_code'] == "FAIL") {
            return ["msg" => $result['return_msg']];
        } elseif (!$result['appid'] || !$result['prepay_id']) {
            return ["msg" => $result['err_code_des']];
        } else {
            $jsapi = [
                "appId"     => $result["appid"],
                "timeStamp" => strval(time()),
                "nonceStr"  => WxPayApi::NonceStr(),
                "package"   => "prepay_id={$result['prepay_id']}",
                "signType"  => $config->$get['sign_type'],
            ];
            $jsapi['paySign'] = WxPayApi::Sign($config, $jsapi);
            return $jsapi;
        }
    }
}
