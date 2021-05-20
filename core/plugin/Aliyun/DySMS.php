<?php
class DYSMS
{
    public static $api   = "https://dysmsapi.aliyuncs.com/";
    public static $cfg   = [];
    public static $input = [];
    public function __construct($config = [])
    {
        global $_L;
        if (!$config) {
            $plugin = LCMS::config([
                "type" => "sys",
                "name" => "config",
                "cate" => "plugin",
            ]);
            $config = $plugin['alisms'];
        }
        self::$cfg   = $config;
        self::$input = [
            "AccessKeyId"      => $config['id'],
            "Action"           => "SendSms",
            "Format"           => "json",
            "SignatureMethod"  => "HMAC-SHA1",
            "SignatureNonce"   => randstr(32),
            "SignatureVersion" => "1.0",
            "Timestamp"        => gmdate("Y-m-d\TH:i:s\Z"),
            "Version"          => "2017-05-25",
        ];
    }
    /**
     * @description: 短信发送
     * @param string $tplcode 模板ID
     * @param string $phone 手机号
     * @param array $tplpara 模板参数
     * @return array
     */
    public function send($tplcode, $phone, $tplpara = [])
    {
        if (strpos($phone, ",") !== false) {
            $phones = explode(",", $phone);
            foreach ($phones as $key => $val) {
                if ($val && is_phone($val)) {
                    $phn[] = $val;
                }
            }
            $PhoneNumbers = implode(",", $phn);
        } elseif (is_phone($phone)) {
            $PhoneNumbers = $phone;
        }
        if ($PhoneNumbers) {
            self::$input = array_merge(self::$input, [
                "PhoneNumbers"  => $PhoneNumbers,
                "SignName"      => self::$cfg['sign'],
                "TemplateCode"  => $tplcode,
                "TemplateParam" => $tplpara ? json_encode_ex($tplpara) : "",
            ]);
            $result = json_decode(http::post(self::$api, $this->sign(), true), true);
            if ($result['Code'] == "OK") {
                $result = ["code" => 1, "msg" => "success"];
            } else {
                $result = ["code" => 0, "msg" => $result['Message']];
            }
        } else {
            $result = ["code" => 0, "msg" => "手机号不能为空"];
        }
        return $result;
    }
    /**
     * @description: 数据签名
     * @param {*}
     * @return {*}
     */
    public function sign()
    {
        $input = self::$input;
        ksort($input);
        foreach ($input as $key => $val) {
            if (!$val) {
                unset($input[$key]);
            } else {
                $sign[] = $this->encode($key) . "=" . $this->encode($val);
            }
        }
        $input['Signature'] = base64_encode(hash_hmac("sha1", "POST&%2F&" . $this->encode(implode("&", $sign)), self::$cfg['secret'] . "&", true));
        return $input;
    }
    /**
     * @description: 签名编码
     * @param string $str
     * @return string
     */
    private function encode($str)
    {
        $str = urlencode($str);
        $str = preg_replace("/\+/", "%20", $str);
        $str = preg_replace("/\*/", "%2A", $str);
        $str = preg_replace("/%7E/", "~", $str);
        return $str;
    }
}