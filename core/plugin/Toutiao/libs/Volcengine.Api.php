<?php
class VolcengineApi
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
     * @description: 请求数据
     * @param string $method
     * @param array $PData
     * @return {*}
     */
    public function reQuest($args)
    {
        $result = json_decode(HTTP::request([
            "type"    => $args['method'],
            "url"     => $args['url'],
            "data"    => $args['data'],
            "headers" => $this->getHeader($args),
        ]), true);
        if ($result['ResponseMetadata']) {
            if ($result['ResponseMetadata']['Error']) {
                $result = [
                    "code" => 0,
                    "msg"  => $result['ResponseMetadata']['Error']['Message'],
                ];
            }
        } else {
            $result = [
                "code" => 0,
                "msg"  => "未知原因错误",
            ];
        }
        return $result;
    }
    /**
     * @description: 获取签名header
     * @param array $args
     * @return array
     */
    public function getHeader($args = [])
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
        }
        $args['uri']               = $urls['path'];
        $args['query']             = $querys;
        $args['headers']['Host']   = $urls['host'];
        $args['headers']['X-Date'] = $utctime;
        $headers                   = "";
        if ($args['headers']) {
            ksort($args['headers']);
            foreach ($args['headers'] as $key => $val) {
                $key = strtolower($key);
                $headers .= "{$key}:{$val}\n";
                $SignedHeaders[] = $key;
            }
            $SignedHeaders = implode(";", $SignedHeaders);
        }
        $CanonicalRequest = "{$args['method']}\n{$args['uri']}\n{$args['query']}\n{$headers}\n{$SignedHeaders}\n" . hash("sha256", $args['data'], false);
        $CanonicalRequest = bin2hex(hash("sha256", $CanonicalRequest, true));
        $CredentialScope  = "{$utcdate}/{$this->cfg['Region']}/{$this->cfg['Service']}/request";
        $StringToSign     = "HMAC-SHA256\n{$utctime}\n{$CredentialScope}\n{$CanonicalRequest}";
        $signingKey       = $this->generateSigningKey($utcdate);
        $Signature        = bin2hex(hash_hmac("sha256", $StringToSign, $signingKey, true));
        $Authorization    = "HMAC-SHA256 Credential={$this->cfg['AccessKeyId']}/{$CredentialScope}, SignedHeaders={$SignedHeaders}, Signature={$Signature}";
        $return           = array_merge($args['headers'], [
            "Authorization" => $Authorization,
        ]);
        ksort($return);
        return $return;
    }
    private function generateSigningKey($date)
    {
        $kDate    = hash_hmac("sha256", $date, $this->cfg['SecretAccessKey'], true);
        $kRegion  = hash_hmac("sha256", $this->cfg['Region'], $kDate, true);
        $kService = hash_hmac("sha256", $this->cfg['Service'], $kRegion, true);
        $kSigning = hash_hmac("sha256", "request", $kService, true);
        return $kSigning;
    }
}
