<?php
load::plugin("payment/alipay/type/lib/AliPay.Api");
/**
 * 企业单笔转账到支付宝
 */
class AliPayTo
{
    public function Order($config, $order)
    {
        $input = [
            'app_id'      => $config->$get['appid'],
            'method'      => 'alipay.fund.trans.toaccount.transfer',
            'format'      => $config->$get['format'],
            'charset'     => $config->$get['charset'],
            'sign_type'   => $config->$get['sign_type'],
            'timestamp'   => $config->$get['timestamp'],
            'version'     => $config->$get['version'],
            'biz_content' => json_encode([
                'out_biz_no'      => $order['order_no'],
                "payee_type"      => "ALIPAY_LOGONID",
                "payee_account"   => $order['account'],
                'amount'          => $order['pay'],
                'payee_real_name' => $order['name'],
                'remark'          => $order['info'],
            ]),
        ];
        $input  = AliPayApi::unifiedOrder($config, $input);
        $result = AliPayApi::post($config->$get['gatewayurl'], $input);
        return json_decode($result, true);
    }
    public function Check($config, $order)
    {
        $input = [
            'app_id'      => $config->$get['appid'],
            'method'      => 'alipay.fund.trans.order.query',
            'format'      => $config->$get['format'],
            'charset'     => $config->$get['charset'],
            'sign_type'   => $config->$get['sign_type'],
            'timestamp'   => $config->$get['timestamp'],
            'version'     => $config->$get['version'],
            'biz_content' => json_encode([
                'out_biz_no' => $order['order_no'],
            ]),
        ];
        $input  = AliPayApi::unifiedOrder($config, $input);
        $result = AliPayApi::post($config->$get['gatewayurl'], $input);
        return json_decode($result, true);
    }
}
