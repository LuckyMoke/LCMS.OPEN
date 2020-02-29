<?php
class CodePayConfig
{
    public $get;
    public function __construct($config)
    {

        $config = [
            "act"      => 1,
            "outTime"  => 360,
            "page"     => 4,
            "pay_type" => "0",
        ] + $config;
        $this->$get = $config;
    }
}
