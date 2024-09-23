<?php
class WxPayApi
{
    /**
     * @description: 签名操作
     * @param array $input
     * @return string
     */
    public static function Sign($config, $input = [])
    {
        if ($input['url']) {
            $input['url'] = self::urlFormat($input['url']);
        }
        openssl_sign(implode("\n", $input) . "\n", $sign, openssl_get_privatekey($config['apiclient_key']), 'sha256WithRSAEncryption');
        if ($input['url']) {
            return "WECHATPAY2-SHA256-RSA2048 " . sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"', $config['mch_id'], $input['nonceStr'], $input['timeStamp'], $config['serial_no'], base64_encode($sign));
        } else {
            return base64_encode($sign);
        }
    }
    /**
     * @description: 敏感信息加密
     * @param array $config
     * @param string $str
     * @return string
     */
    public static function getEncrypt($config, $string = "")
    {
        $encrypted = "";
        $publicKey = openssl_pkey_get_details(openssl_pkey_get_public($config['apiclient_cert']))['key'];
        $sLength   = mb_strlen($string);
        $offet     = 0;
        $i         = 0;
        while ($sLength - $offet > 0) {
            if ($sLength - $offet > 128) {
                $str = mb_substr($string, $offet, 128);
            } else {
                $str = mb_substr($string, $offet, $sLength - $offet);
            }
            $cache = "";
            openssl_public_encrypt($str, $cache, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
            $encrypted .= $cache;
            $i++;
            $offet = $i * 128;
        }
        return base64_encode($encrypted);
    }
    /**
     * @description: API请求
     * @param string $method
     * @param string $url
     * @param array $config
     * @param string $body
     * @return string
     */
    public static function Request($method = "POST", $url, $config = [], $body = "")
    {
        $Authorization = self::Sign($config, [
            "method"    => $method,
            "url"       => $url,
            "timeStamp" => time(),
            "nonceStr"  => randstr(32, "let"),
            "body"      => $body,
        ]);
        switch ($method) {
            case 'POST':
                return json_decode(HTTP::request([
                    "type"    => "POST",
                    "url"     => $url,
                    "data"    => $body,
                    "headers" => [
                        "Authorization" => $Authorization,
                        "Content-Type"  => "application/json; charset=utf-8",
                        "Accept"        => "application/json",
                    ],
                ]), true);
                break;
            case 'GET':
                return json_decode(HTTP::request([
                    "type"    => "GET",
                    "url"     => $url,
                    "headers" => [
                        "Authorization" => $Authorization,
                    ],
                ]), true);
                break;
        }
    }
    /**
     * @description: 链接格式化
     * @param string $url
     * @return array
     */
    private static function urlFormat($url = "")
    {
        if (is_url($url)) {
            $url = parse_url($url);
            return $url['path'] . (!empty($url['query']) ? "?{$url['query']}" : "");
        } else {
            LCMS::X("403", "请求链接错误");
        }
    }
}
