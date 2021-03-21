<?php
load::plugin("payment/alipay/type/lib/AliPay.Api");
class AliPayApp
{
    public function Order($config, $order)
    {
        $input = [
            'method'      => 'alipay.trade.app.pay',
            'app_id'      => $config->get['appid'],
            'format'      => $config->get['format'],
            'charset'     => $config->get['charset'],
            'sign_type'   => $config->get['sign_type'],
            'timestamp'   => $config->get['timestamp'],
            'version'     => $config->get['version'],
            'notify_url'  => $config->get['notify_url'],
            'biz_content' => json_encode([
                'out_trade_no' => $order['order_no'],
                'total_amount' => $order['pay'],
                'subject'      => $order['body'],
                'goods_type'   => "1",
                'product_code' => "QUICK_MSECURITY_PAY",
            ] + ($order['fenqi'] ?: [])),
        ];
        $input = AliPayApi::unifiedOrder($config, $input);
        return $this->buildRequestStr($config, $input);
    }
    protected function buildRequestStr($config, $input)
    {
        foreach ($input as $key => $val) {
            $arr[] = $key . "=" . urlencode($val);
        }
        $result = implode("&", $arr);
        return $result;
    }
}
