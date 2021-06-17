<?php
class AliPayApi
{
    /**
     * @description: 数据签名
     * @param array $config
     * @param string $input
     * @return array
     */
    public static function Sign($config, $input = [])
    {
        if ($config['privatekey']) {
            $config['privatekey'] = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($config['privatekey'], 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
            openssl_sign(self::ToUrlParams($input), $sign, $config['privatekey'], OPENSSL_ALGO_SHA256);
            $input['sign'] = base64_encode($sign);
            return $input;
        } else {
            LCMS::X(401, "缺少证书信息");
        }
    }
    /**
     * @description: 签名验证
     * @param array $config
     * @param array $input
     * @return bool
     */
    public static function Verify($config, $input = [])
    {
        $sign     = base64_decode($input['sign']);
        $signType = $input['sign_type'];
        unset($input['sign'], $input['sign_type']);
        if ($config['publickey']) {
            $config['publickey'] = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($config['publickey'], 64, "\n", true) . "\n-----END PUBLIC KEY-----";
            if ($signType == "RSA2") {
                $result = (bool) openssl_verify(self::ToUrlParams($input), $sign, $config['publickey'], OPENSSL_ALGO_SHA256);
            } else {
                $result = (bool) openssl_verify(self::ToUrlParams($input), $sign, $config['publickey']);
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
    public static function ToUrlParams($input = [])
    {
        ksort($input);
        foreach ($input as $key => $val) {
            if (!self::checkEmpty($val) && substr($val, 0, 1) != "@") {
                $arr[] = "{$key}={$val}";
            }
        }
        return implode("&", $arr ?: []);
    }
    public static function checkEmpty($str)
    {
        if (!isset($str) || $str === null || trim($str) === "") {
            return true;
        }
        return false;
    }
}
