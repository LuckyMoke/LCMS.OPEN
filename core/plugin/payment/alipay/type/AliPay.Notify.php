<?php
load::plugin("payment/alipay/type/lib/AliPay.Api");
class AliPayNotify
{
    public function check()
    {
        $postObj = $_POST;
        if ($postObj['out_trade_no']) {
            $order = sql_get(["order", "order_no = '{$postObj['out_trade_no']}'"]);
            if ($order['payid'] && $order['status'] == "0") {
                $payment  = LCMS::form(array("table" => "payment", "do" => "get", "id" => $order['payid']));
                $sign     = $postObj['sign'];
                $signType = $postObj['sign_type'];
                unset($postObj['sign_type']);
                unset($postObj['sign']);
                $result = AliPayApi::verify(AliPayApi::ToUrlParams($postObj), $sign, $payment['alipay']['publickey'], $signType);
                if ($result === true && $postObj['total_amount'] == $order['pay'] && $postObj['trade_status'] == "TRADE_SUCCESS") {
                    return $order;
                }
            }
        }
    }
}
