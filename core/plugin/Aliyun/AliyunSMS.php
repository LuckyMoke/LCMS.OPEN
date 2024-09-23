<?php
class AliyunSMS
{
    public $api   = "https://dysmsapi.aliyuncs.com/";
    public $cfg   = [];
    public $input = [];
    /**
     * @description: 初始化配置
     * @param array $config
     * @return {*}
     */
    public function __construct($config = [])
    {
        global $_L;
        $this->cfg   = $config;
        $this->input = [
            "AccessKeyId"      => $this->cfg['AccessKeyId'],
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
     * @description: 发送短信
     * @param array|string $Phone 手机号
     * @param array $Param 模板参数
     * @return {*}
     */
    public function send($Phone = "", $Param = [])
    {
        $PN = [];
        if (is_array($Phone)) {
            foreach ($Phone as $val) {
                if ($val && is_phone($val)) {
                    $PN[] = "+86{$val}";
                }
            }
        } elseif (is_phone($Phone)) {
            $PN[] = "+86{$Phone}";
        }
        if ($PN) {
            $this->input = array_merge($this->input, [
                "PhoneNumbers"  => implode(",", $PN),
                "SignName"      => $this->cfg['SignName'],
                "TemplateCode"  => $this->cfg['TemplateCode'],
                "TemplateParam" => $Param ? json_encode_ex($Param) : "",
            ]);
            $result = json_decode(HTTP::request([
                "type"  => "POST",
                "url"   => $this->api,
                "data"  => $this->sign(),
                "build" => true,
            ]), true);
            if ($result['Code'] === "OK") {
                $result = [
                    "code" => 1,
                    "msg"  => "发送成功",
                    "data" => $PN,
                ];
            } else {
                $result = [
                    "code" => 0,
                    "msg"  => $result['Message'],
                ];
            }
        } else {
            $result = [
                "code" => 0,
                "msg"  => "手机号不能为空",
            ];
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
        $input = $this->input;
        ksort($input);
        foreach ($input as $key => $val) {
            if (!$val) {
                unset($input[$key]);
            } else {
                $sign[] = $this->encode($key) . "=" . $this->encode($val);
            }
        }
        $input['Signature'] = base64_encode(hash_hmac("sha1", "POST&%2F&" . $this->encode(implode("&", $sign)), $this->cfg['AccessKeySecret'] . "&", true));
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
