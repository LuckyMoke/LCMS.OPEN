<?php
require_once "AliPay.Api.php";
require_once "AliPay.Order.php";
require_once "AliPay.Notify.php";
class AliPayConfig
{
    public $get = [];
    public function __construct($config = [])
    {
        $this->get = array_merge([
            "sign_type"       => "RSA2",
            "charset"         => "utf-8",
            "format"          => "json",
            "timestamp"       => date("Y-m-d H:i:s"),
            "version"         => "1.0",
            "timeout_express" => "2h",
        ], $config, [
            "gatewayurl" => $config['gatewayurl'] == 1 ? "https://openapi-sandbox.dl.alipaydev.com/gateway.do" : "https://openapi.alipay.com/gateway.do",
        ]);
    }
}
