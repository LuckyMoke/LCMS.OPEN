<?php
class WxPayApi
{
    /**
     * [checkEmpty 检测字符串是否为空]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function checkEmpty($value)
    {
        if (!isset($value) || $value === null || trim($value) === "") {
            return true;
        }
        return false;
    }
    /**
     * [ToUrlParams 数组转URL字符串]
     * @param [type] $para [description]
     */
    public static function ToUrlParams($para = [])
    {
        ksort($para);
        foreach ($para as $key => $val) {
            if ($key != "sign" && !self::checkEmpty($val) && !is_array($val)) {
                $arr[] = "{$key}={$val}";
            }
        }
        return implode("&", $arr);
    }
    /**
     * [Sign 签名算法]
     * @param [type]  $config       [description]
     * @param boolean $para         [description]
     */
    public static function Sign($config, $para)
    {
        return strtoupper(md5(self::ToUrlParams($para) . "&key=" . $config->get['key']));
    }
    /**
     * [ToXml 数组转XML]
     * @param [type] $para [description]
     */
    public static function ToXml($para = [])
    {
        $xml = "<xml>";
        foreach ($para as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    /**
     * [FromXml XML转数组]
     * @param [type] $xml [description]
     */
    public static function FromXml($xml = "")
    {
        if ($xml) {
            libxml_disable_entity_loader(true);
            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        }
        return $result;
    }
    /**
     * [NonceStr 获取随机字符串]
     * @param  integer $length [description]
     * @return [type]          [description]
     */
    public static function NonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * [postXmlCurl POST XML数据]
     * @param  [type]  $config  [description]
     * @param  [type]  $xml     [description]
     * @param  [type]  $url     [description]
     * @param  boolean $useCert [description]
     * @param  integer $second  [description]
     * @return [type]           [description]
     */
    public static function postXmlCurl($config, $xml, $url, $useCert = false, $second = 30)
    {
        $ch          = curl_init();
        $curlVersion = curl_version();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        if ($config->get['proxy_host'] != "0.0.0.0" && $config->get['proxy_port'] != 0) {
            curl_setopt($ch, CURLOPT_PROXY, $config->get['proxy_host']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $config->get['proxy_port']);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "WXPaySDK/" . $config->get['version'] . " (" . PHP_OS . ") PHP/" . PHP_VERSION . " CURL/" . $curlVersion['version'] . " " . $config->get['mch_id']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($useCert == true) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $config->get['apiclient_cert']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $config->get['apiclient_key']);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
