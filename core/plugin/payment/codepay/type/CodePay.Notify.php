<?php
class CodePayNotify
{
    public function check()
    {
        $postObj = $_POST;
        if ($postObj['pay_id'] && $postObj['pay_no']) {
            $order = sql_get(["order", "order_no = '{$postObj['pay_id']}'"]);
            if ($order['payid'] && $order['status'] == "0") {
                $payment = LCMS::form([
                    "table" => "payment",
                    "do"    => "get",
                    "id"    => $order['payid'],
                ]);
                if ($this->verify($payment, $postObj) && $postObj['price'] == $order['pay']) {
                    return [
                        "order"    => $order,
                        "response" => $postObj,
                    ];
                } else {
                    exit("fail");
                }
            }
        }
    }
    protected function verify($payment, $postObj)
    {
        ksort($postObj);
        reset($postObj);
        $sign = '';
        foreach ($postObj as $key => $val) {
            if ($val == '' || $key == 'sign') {
                continue;
            }
            if ($sign) {
                $sign .= '&';
            }
            $sign .= "$key=$val";
        }
        if (md5($sign . $payment['codepay']['appsecret']) != $postObj['sign']) {
            return false;
        } else {
            return true;
        }
    }
}
