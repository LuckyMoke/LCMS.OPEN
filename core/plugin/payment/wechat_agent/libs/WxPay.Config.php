<?php
require_once "WxPay.Api.php";
require_once "WxPay.Order.php";
require_once "WxPay.Notify.php";
class WxPayConfig
{
    public $get = [];
    public function __construct($config = [], $sign_type = "RSA")
    {
        $this->get = array_merge([
            "sign_type" => $sign_type,
        ], $config);
    }
}
