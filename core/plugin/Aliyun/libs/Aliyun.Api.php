<?php
class AliyunApi
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
     * @description: 获取签名
     * @param array $args
     * @return array
     */
    public function sign($args = [])
    {

        $utcdate = gmdate("Ymd");
        $utctime = gmdate("Ymd\THis\Z");
        $args    = array_merge([
            "method" => "GET",
        ], $args);
        $urls = parse_url($args['url']);
        if ($urls['query']) {
            parse_str($urls['query'], $querys);
            ksort($querys);
            $querys = http_build_query($querys);
            $querys = trim($querys, "=");
        }
        $args['uri']                             = $urls['path'];
        $args['query']                           = $querys;
        $args['headers']['Host']                 = $urls['host'];
        $args['headers']['x-oss-date']           = $utctime;
        $args['headers']['x-oss-content-sha256'] = "UNSIGNED-PAYLOAD";
        $headers                                 = "";
        if ($args['headers']) {
            ksort($args['headers']);
            foreach ($args['headers'] as $key => $val) {
                $key = strtolower($key);
                $headers .= "{$key}:{$val}\n";
                $SignedHeaders[] = $key;
            }
            $SignedHeaders = implode(";", $SignedHeaders);
        }
        $CanonicalRequest = "{$args['method']}\n{$args['uri']}\n{$args['query']}\n{$headers}\n{$SignedHeaders}\nUNSIGNED-PAYLOAD";
        $CanonicalRequest = bin2hex(hash("sha256", $CanonicalRequest, true));
        $CredentialScope  = "{$utcdate}/{$this->cfg['Region']}/{$this->cfg['Service']}/aliyun_v4_request";
        $StringToSign     = "{$this->cfg['Alg']}\n{$utctime}\n{$CredentialScope}\n{$CanonicalRequest}";
        $signingKey       = $this->generateSigningKey($utcdate);
        $Signature        = bin2hex(hash_hmac("sha256", $StringToSign, $signingKey, true));
        $Authorization    = "{$this->cfg['Alg']} Credential={$this->cfg['AccessKeyId']}/{$CredentialScope},AdditionalHeaders={$SignedHeaders},Signature={$Signature}";
        $return           = array_merge($args['headers'], [
            "Authorization" => $Authorization,
        ]);
        ksort($return);
        return $return;

    }
    private function generateSigningKey($date)
    {
        $kDate    = hash_hmac("sha256", $date, "aliyun_v4" . $this->cfg['AccessKeySecret'], true);
        $kRegion  = hash_hmac("sha256", $this->cfg['Region'], $kDate, true);
        $kService = hash_hmac("sha256", $this->cfg['Service'], $kRegion, true);
        $kSigning = hash_hmac("sha256", "aliyun_v4_request", $kService, true);
        return $kSigning;
    }
}
