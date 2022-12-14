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
            return sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"', $config['mch_id'], $input['nonceStr'], $input['timeStamp'], $config['serial_no'], base64_encode($sign));
        } else {
            return base64_encode($sign);
        }
    }
    /**
     * @description: API请求
     * @param string $method
     * @param string $url
     * @param string $body
     * @param array $headers
     * @return string
     */
    public static function Request($method = 'POST', $url, $body = "", $headers = [])
    {
        $ch = curl_init($url);
        if ($headers) {
            foreach ($headers as $key => $val) {
                $header[] = "{$key}:{$val}";
            }
            $header[] = "User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
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
