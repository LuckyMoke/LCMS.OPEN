<?php
class WxPayConfig
{
    public $get;
    public function __construct($config)
    {

        $config = [
            "version"    => "3.0.10",
            "sign_type"  => "MD5",
            "proxy_host" => "0.0.0.0",
            "proxy_port" => "0",
        ] + $config;
        $this->$get = $config;
    }
}