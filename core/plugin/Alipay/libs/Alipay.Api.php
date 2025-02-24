<?php
class AlipayApi
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
    }
    /**
     * @description: 数据签名
     * @param array $input
     * @return array
     */
    public function sign($input = [])
    {
        if ($this->cfg['privatekey']) {
            $this->cfg['privatekey'] = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->cfg['privatekey'], 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
            openssl_sign($this->ToUrlParams($input), $sign, $this->cfg['privatekey'], OPENSSL_ALGO_SHA256);
            $input['sign'] = base64_encode($sign);
            return $input;
        } else {
            LCMS::X(401, "缺少证书信息");
        }
    }
    /**
     * @description: 签名验证
     * @param array $input
     * @return bool
     */
    public function verify($input = [])
    {
        $sign     = base64_decode($input['sign']);
        $signType = $input['sign_type'];
        unset($input['sign'], $input['sign_type']);
        if ($this->cfg['publickey']) {
            $this->cfg['publickey'] = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->cfg['publickey'], 64, "\n", true) . "\n-----END PUBLIC KEY-----";
            if ($signType === "RSA2") {
                $result = (bool) openssl_verify($this->ToUrlParams($input), $sign, $this->cfg['publickey'], OPENSSL_ALGO_SHA256);
            } else {
                $result = (bool) openssl_verify($this->ToUrlParams($input), $sign, $this->cfg['publickey']);
            }
            return $result;
        } else {
            LCMS::X(401, "缺少证书信息");
        }
    }
    /**
     * @description: 数据转字符串
     * @param array $input
     * @return string
     */
    private function ToUrlParams($input = [])
    {
        ksort($input);
        foreach ($input as $key => $val) {
            if (!$this->checkEmpty($val) && substr($val, 0, 1) != "@") {
                $arr[] = "{$key}={$val}";
            }
        }
        return implode("&", $arr ?: []);
    }
    private function checkEmpty($str)
    {
        if (!isset($str) || $str === null || trim($str) === "") {
            return true;
        }
        return false;
    }
}
