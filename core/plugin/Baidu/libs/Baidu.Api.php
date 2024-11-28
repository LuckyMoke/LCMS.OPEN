<?php
class BaiduApi
{
    public $cfg = [];
    /**
     * @description: 初始化配置
     * @param array $config
     * @return {*}
     */
    public function __construct($config)
    {
        $this->cfg = $config;
    }
    /**
     * @description: 签名
     * @param string $method
     * @param string $path
     * @param string $query
     * @return string
     */
    public function sign($method, $api)
    {
        $urls    = parse_url($api);
        $date    = gmdate("Y-m-d\TH:i:s\Z");
        $ipnut   = "{$method}\n{$urls['path']}\n{$urls['query']}\nhost:{$urls['host']}";
        $signPre = "bce-auth-v1/{$this->cfg['AccessKey']}/{$date}/1800";
        $signKey = hash_hmac("sha256", $signPre, $this->cfg['SecretKey']);
        return [
            "api"  => $api,
            "host" => $urls['host'],
            "date" => $date,
            "sign" => "{$signPre}/host/" . hash_hmac("sha256", $ipnut, $signKey),
        ];
    }
}
