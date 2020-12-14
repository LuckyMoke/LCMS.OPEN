<?php
class AliPayConfig
{
    public $get;
    public function __construct($config)
    {

        $config = [
            "sign_type"       => "RSA2",
            "charset"         => "utf-8",
            "format"          => "json",
            "timestamp"       => date("Y-m-d H:i:s"),
            "version"         => "1.0",
            "timeout_express" => "2h",
        ] + $config;
        $config['gatewayurl'] = $config['gatewayurl'] == "1" ? "https://openapi.alipaydev.com/gateway.do" : "https://openapi.alipay.com/gateway.do";
        $this->get            = $config;
    }
}
