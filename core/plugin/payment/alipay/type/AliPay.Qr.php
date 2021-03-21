<?php
load::plugin("payment/alipay/type/lib/AliPay.Api");
class AliPayQr
{
    public function Order($config, $order)
    {
        $input = [
            'method'      => 'alipay.trade.precreate',
            'app_id'      => $config->get['appid'],
            'format'      => $config->get['format'],
            'charset'     => $config->get['charset'],
            'sign_type'   => $config->get['sign_type'],
            'timestamp'   => $config->get['timestamp'],
            'version'     => $config->get['version'],
            'notify_url'  => $config->get['notify_url'],
            'biz_content' => json_encode([
                'out_trade_no'    => $order['order_no'],
                'total_amount'    => $order['pay'],
                'subject'         => $order['body'],
                'goods_type'      => "1",
                'timeout_express' => $config->get['timeout_express'],
            ] + ($order['fenqi'] ?: [])),
        ];
        $input  = AliPayApi::unifiedOrder($config, $input);
        $result = AliPayApi::post($config->get['gatewayurl'], $input);
        return json_decode($result, true);
    }
}
