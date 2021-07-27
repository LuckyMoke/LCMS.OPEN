<?php
class AliyunEMAIL
{
    public $api   = "https://dm.aliyuncs.com/";
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
            "Action"           => "SingleSendMail",
            "Format"           => "json",
            "SignatureMethod"  => "HMAC-SHA1",
            "SignatureNonce"   => randstr(32),
            "SignatureVersion" => "1.0",
            "Timestamp"        => gmdate("Y-m-d\TH:i:s\Z"),
            "Version"          => "2015-11-23",
        ];
    }
    /**
     * @description: 发送邮件
     * @param array|string $TO 收件人邮箱
     * @param string $Title 邮件标题
     * @param string $Body 邮件内容
     * @param array $Att 邮件附件
     * @return {*}
     */
    public function send($TO = "", $Title = "", $Body = "", $Att = [])
    {
        $EM = [];
        if (is_array($TO)) {
            foreach ($TO as $val) {
                if ($val && is_email($val)) {
                    $PN[] = $val;
                }
            }
        } elseif (is_email($TO)) {
            $EM[] = $TO;
        }
        if ($EM) {
            $this->input = array_merge($this->input, [
                "AccountName"    => $this->cfg['From'],
                "AddressType"    => 1,
                "ReplyToAddress" => $this->cfg['Reply'] ? "true" : "false",
                "FromAlias"      => $this->cfg['Alias'],
                "Subject"        => $Title,
                "ToAddress"      => implode(",", $EM),
            ]);
            if (is_array($Body)) {
                // 阿里云不支持邮件模板
            } else {
                $this->input = array_merge($this->input, [
                    "HtmlBody" => $Body,
                ]);
            }
            $result = json_decode(HTTP::post($this->api, $this->sign(), true), true);
            if ($result['EnvId']) {
                $result = [
                    "code" => 1,
                    "msg"  => "success",
                    "data" => $EM,
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
                "msg"  => "收件地址不能为空",
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
