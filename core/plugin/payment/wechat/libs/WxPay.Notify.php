<?php
class WxPayNotify
{
    public function Check($payname)
    {
        global $_L;
        $body = file_get_contents("php://input");
        $ret  = json_decode($body, true);
        if ($ret && $ret['event_type'] == "TRANSACTION.SUCCESS") {
            // 如果订单号存在，需要伪静态加一个订单号
            if ($_L['form']['orderno']) {
                $order = sql_get([
                    "order",
                    "order_no = :orderno",
                    "", [
                        ":orderno" => $_L['form']['orderno'],
                    ],
                ]);
                if ($order['payid'] && $order['status'] == "0") {
                    $payment = LCMS::form([
                        "table" => "payment",
                        "do"    => "get",
                        "id"    => $order['payid'],
                    ]);
                    $config = new WxPayConfig($payment[$payname]);
                    if ($config->get['key']) {
                        $response = sodium_crypto_aead_aes256gcm_decrypt(base64_decode($ret['resource']['ciphertext']), $ret['resource']['associated_data'], $ret['resource']['nonce'], $config->get['key']);
                        $response = json_decode($response, true);
                        if ($response && $response['trade_state'] === "SUCCESS" && intval($order['pay'] * 100) == $response['amount']['total'] && $response['out_trade_no'] === $order['order_no']) {
                            return [
                                "order"    => $order,
                                "response" => $response,
                            ];
                        }
                    }
                }
            }
        }
        LCMS::X(403, "非法请求");
    }
}
