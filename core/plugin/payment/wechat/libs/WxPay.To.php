<?php
class WxPayTo
{
    /**
     * @description: 接口初始化
     * @param array $init
     * @return {*}
     */
    public function __construct($init)
    {
        $this->api   = "https://api.mch.weixin.qq.com";
        $this->cfg   = $init['config'];
        $this->order = $init['order'];
    }
    /**
     * @description: 微信付款到红包
     * @param {*}
     * @return array
     */
    public function couPon()
    {
        $url   = "/mmpaymkttransfers/sendredpack";
        $input = [
            "mch_id"       => $this->cfg['mch_id'],
            "wxappid"      => $this->cfg['appid'],
            "mch_billno"   => $this->order['order_no'],
            "send_name"    => $this->order['send_name'],
            "re_openid"    => $this->order['openid'],
            "total_amount" => $this->order['pay'] * 100,
            "total_num"    => 1,
            "wishing"      => $this->order['wishing'],
            "client_ip"    => SERVER_IP,
            "act_name"     => $this->order['act_name'],
            "remark"       => $this->order['remark'],
            "scene_id"     => $this->order['pay'] < "1.00" || $this->order['pay'] > "200.00" ? "PRODUCT_1" : "",
            "nonce_str"    => randstr(32, "let"),
        ];
        $input['sign'] = $this->Sign($input);
        return $this->FromXml($this->postXmlCurl($url, $input));
    }
    /**
     * @description: 微信付款到零钱
     * @param {*}
     * @return {*}
     */
    public function Pay()
    {
        $url   = "/mmpaymkttransfers/promotion/transfers";
        $input = [
            "mch_appid"        => $this->cfg['appid'],
            "mchid"            => $this->cfg['mch_id'],
            "partner_trade_no" => $this->order['order_no'],
            "openid"           => $this->order['openid'],
            "check_name"       => "NO_CHECK",
            "amount"           => $this->order['pay'] * 100,
            "desc"             => $this->order['desc'],
            "spbill_create_ip" => SERVER_IP,
            "nonce_str"        => randstr(32, "let"),
        ];
        $input['sign'] = $this->Sign($input);
        return $this->FromXml($this->postXmlCurl($url, $input));
    }
    /**
     * @description: 数据签名
     * @param array $input
     * @return string
     */
    private function Sign($input = [])
    {
        return strtoupper(md5($this->ToUrlParams($input) . "&key=" . $this->cfg['key']));
    }
    private function ToUrlParams($input = [])
    {
        ksort($para);
        foreach ($para as $key => $val) {
            if (!$this->checkEmpty($val)) {
                $arr[] = "{$key}={$val}";
            }
        }
        return implode("&", $arr ?: []);
    }
    private function checkEmpty($str = "")
    {
        if (!isset($str) || $str === null || trim($str) === "") {
            return true;
        }
        return false;
    }
    private function ToXml($input = [])
    {
        $xml = "<xml>";
        foreach ($input as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    private function getTmpCert($cert = "")
    {
        if ($cert) {
            static $tmpFile = null;
            $tmpFile        = tmpfile();
            fwrite($tmpFile, $cert);
            $tempPemPath = stream_get_meta_data($tmpFile);
            return $tempPemPath['uri'];
        } else {
            LCMS::X(404, "未找到支付证书");
        }
    }
    private function FromXml($xml = "")
    {
        if ($xml) {
            if (PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        }
        return $result ?: [];
    }
    private function postXmlCurl($url, $input, $Cert = true)
    {
        $input = $this->ToXml($input);
        $ch    = curl_init();
        $curlV = curl_version();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, "WXPaySDK/3.0.10 (" . PHP_OS . ") PHP/" . PHP_VERSION . " CURL/" . $curlV['version'] . " " . $this->cfg['mch_id']);
        curl_setopt($ch, CURLOPT_URL, $this->api . $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($Cert == true) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->getTmpCert($this->cfg['apiclient_cert']));
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $this->getTmpCert($this->cfg['apiclient_key']));
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
