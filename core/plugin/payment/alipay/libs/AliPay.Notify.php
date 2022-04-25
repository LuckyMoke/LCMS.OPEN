<?php
class AliPayNotify
{
    public function Check($payname)
    {
        global $_L;
        $POST = $_POST;
        if ($POST['out_trade_no']) {
            $order = sql_get([
                "order",
                "order_no = :orderno",
                "", [
                    ":orderno" => $POST['out_trade_no'],
                ],
            ]);
            if ($order['payid'] && $order['status'] == "0") {
                $payment = LCMS::form([
                    "table" => "payment",
                    "do"    => "get",
                    "id"    => $order['payid'],
                ]);
                $result = AliPayApi::Verify($payment[$payname], $POST);
                if ($result === true && $POST['total_amount'] == $order['pay'] && $POST['trade_status'] === "TRADE_SUCCESS") {
                    return [
                        "order"    => $order,
                        "response" => $POST,
                    ];
                }
            }
        }
        LCMS::X(403, "非法请求");
    }
}
