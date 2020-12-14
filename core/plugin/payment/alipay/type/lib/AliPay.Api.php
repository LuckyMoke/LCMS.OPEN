<?php
class AliPayApi
{
    public static function unifiedOrder($config, $input)
    {
        $input['sign'] = self::sign(self::ToUrlParams($input), $config->get['privatekey'], $config->get['sign_type']);
        return $input;
    }
    protected static function sign($para, $key = "", $signType = "RSA")
    {
        if ($key) {
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
            if ($signType == "RSA2") {
                openssl_sign($para, $sign, $key, OPENSSL_ALGO_SHA256);
            } else {
                openssl_sign($para, $sign, $key);
            }
            $sign = base64_encode($sign);
            return $sign;
        }
        return false;
    }
    public static function verify($para, $sign, $key = "", $signType = 'RSA')
    {
        if ($key) {
            $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
            if ($signType == "RSA2") {
                $result = (bool) openssl_verify($para, base64_decode($sign), $key, OPENSSL_ALGO_SHA256);
            } else {
                $result = (bool) openssl_verify($para, base64_decode($sign), $key);
            }
            return $result;
        }
        return false;
    }
    public static function ToUrlParams($para = array())
    {
        ksort($para);
        foreach ($para as $key => $val) {
            if (!self::checkEmpty($val) && substr($val, 0, 1) != "@") {
                $arr[] = "{$key}={$val}";
            }
        }
        return implode("&", $arr);
    }
    public static function checkEmpty($value)
    {
        if (!isset($value) || $value === null || trim($value) === "") {
            return true;
        }
        return false;
    }
    public static function utf8($content = "")
    {
        if (!empty($content)) {
            return mb_convert_encoding($content, "UTF-8", "GBK2312, GBK, BIG5, ASCII");
        }
    }
    public static function post($url, $data, $build = true)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type:application/x-www-form-urlencoded;charset=UTF-8"]);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $build ? http_build_query($data) : $data);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
