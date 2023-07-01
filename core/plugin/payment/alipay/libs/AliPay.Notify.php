<?php
class AliPayNotify
{
    public function Check($payname)
    {
        global $_L;
        $POST = $_POST;
        $POST['out_trade_no'] || LCMS::X(403, "非法请求");
        $order = sql_get([
            "table" => "order",
            "where" => "order_no = :order_no",
            "bind"  => [
                ":order_no" => $POST['out_trade_no'],
            ],
        ]);
        if ($order['payid'] && $order['status'] == 0) {
            $payinfo = LCMS::form([
                "table" => "payment",
                "do"    => "get",
                "id"    => $order['payid'],
            ]);
            $result = AliPayApi::Verify($payinfo[$payname], $POST);
            if ($result === true && $POST['total_amount'] == $order['pay'] && $POST['trade_status'] === "TRADE_SUCCESS") {
                return [
                    "order"    => $order,
                    "response" => $POST,
                ];
            }
        }
    }
}
