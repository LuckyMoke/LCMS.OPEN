<?php
class TencentApi
{
    public $cfg = [];
    /**
     * @description: 初始化配置
     * @param array $config
     * @return {*}
     */
    public function __construct($config)
    {
        $this->cfg            = $config;
        $this->cfg['Region']  = $config['Region'] ?: "ap-beijing";
        $this->cfg['ProName'] = $config['ProName'] ?: explode(".", $config['Host'])[0];
    }
    /**
     * @description: 获取签名header
     * @param string $method
     * @param string $PData
     * @return {*}
     */
    public function getHeader($method = "POST", $PData)
    {
        $time    = time();
        $utcdate = date("Y-m-d", $time - 28800);

        $StringToSign = $method . "\n";
        $StringToSign .= "/" . "\n";
        $StringToSign .= "" . "\n";
        $StringToSign .= "content-type:application/json" . "\n";
        $StringToSign .= "host:{$this->cfg['Host']}" . "\n";
        $StringToSign .= "" . "\n";
        $StringToSign .= "content-type;host" . "\n";
        $StringToSign .= hash("SHA256", $PData);
        $StringToSign = "TC3-HMAC-SHA256\n{$time}\n{$utcdate}/{$this->cfg['ProName']}/tc3_request\n" . hash("SHA256", $StringToSign);

        $SecretSigning = hash_hmac('SHA256', $utcdate, "TC3" . $this->cfg['secretkey'], true);
        $SecretSigning = hash_hmac('SHA256', $this->cfg['ProName'], $SecretSigning, true);
        $SecretSigning = hash_hmac('SHA256', "tc3_request", $SecretSigning, true);
        $Signature     = hash_hmac('SHA256', $StringToSign, $SecretSigning);

        $Authorization = "TC3-HMAC-SHA256 ";
        $Authorization .= "Credential=" . $this->cfg['secretId'] . "/{$utcdate}/{$this->cfg['ProName']}/tc3_request, ";
        $Authorization .= "SignedHeaders=content-type;host, ";
        $Authorization .= "Signature=" . $Signature;

        return [
            "Authorization: " . $Authorization,
            "Content-Type: application/json",
            "Host: {$this->cfg['Host']}",
            "X-TC-Action: {$this->cfg['Action']}",
            "X-TC-Region: {$this->cfg['Region']}",
            "X-TC-Timestamp: " . $time,
            "X-TC-Version: {$this->cfg['Version']}",
        ];
    }
    /**
     * @description: 请求数据
     * @param string $method
     * @param array $PData
     * @return {*}
     */
    public function reQuest($method = "POST", $PData)
    {
        if ($method == "POST") {
            $PData  = json_encode($PData, JSON_UNESCAPED_UNICODE);
            $result = json_decode(HTTP::post("https://{$this->cfg['Host']}/", $PData, false, $this->getHeader($method, $PData)), true);
        }
        if ($result['Response']) {
            $result = $result['Response'];
            if ($result['Error']) {
                $result = [
                    "code" => 0,
                    "msg"  => $result['Error']['Message'],
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
}
