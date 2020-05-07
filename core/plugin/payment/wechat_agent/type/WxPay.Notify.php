<?php
load::plugin("payment/wechat_agent/type/lib/WxPay.Api");
load::plugin("payment/wechat_agent/Config");
class WxPayNotify
{
    public function check()
    {
        global $_L;
        $postObj = WxPayApi::FromXml(file_get_contents("php://input"));
        if ($postObj['out_trade_no']) {
            $order = sql_get(["order", "order_no = '{$postObj['out_trade_no']}'"]);
            if ($order['payid'] && $order['status'] == "0") {
                $payment = LCMS::form([
                    "table" => "payment",
                    "do"    => "get",
                    "id"    => $order['payid'],
                ]);
                if ($payment) {
                    $config = new WxPayConfig($payment['wechat_agent']);
                    if ($postObj['result_code'] == 'SUCCESS' && WxPayApi::Sign($config, $postObj) === $postObj['sign'] && ($postObj['total_fee'] / 100) == $order['pay']) {
                        return [
                            "order"    => $order,
                            "response" => $postObj,
                        ];
                    }
                }
            }
        }
        return false;
    }
}
