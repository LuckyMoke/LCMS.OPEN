<?php
load::plugin("payment/alipay/type/lib/AliPay.Api");
class AliPayCheck
{
    public function Check($config, $order)
    {
        global $_L;
        $input = [
            'app_id'      => $config->$get['appid'],
            'method'      => 'alipay.trade.query',
            'format'      => $config->$get['format'],
            'charset'     => $config->$get['charset'],
            'sign_type'   => $config->$get['sign_type'],
            'timestamp'   => $config->$get['timestamp'],
            'version'     => $config->$get['version'],
            'biz_content' => json_encode([
                "out_trade_no"  => $order['order_no'],
                "refund_amount" => $order['pay'],
            ]),
        ];
        $input  = AliPayApi::unifiedOrder($config, $input);
        $result = AliPayApi::post($config->$get['gatewayurl'], $input);
        $result = json_decode($result, true);
        return $result['alipay_trade_query_response'];
    }
}
