<?php
require "libs/Tencent.Api.php";
class TencentSMS
{
    public $cfg = [];
    /**
     * @description: 初始化配置
     * @param array $config
     * @return {*}
     */
    public function __construct($config)
    {
        $this->cfg = $config;
        $this->Api = new TencentApi([
            "secretId"  => $this->cfg['secretId'],
            "secretkey" => $this->cfg['secretkey'],
            "Host"      => "sms.tencentcloudapi.com",
            "Action"    => "SendSms",
            "Version"   => "2021-01-11",
        ]);
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
            $result = $this->Api->reQuest("POST", [
                "SmsSdkAppId"      => $this->cfg['SmsSdkAppId'],
                "TemplateId"       => $this->cfg['TemplateId'],
                "SignName"         => $this->cfg['SignName'],
                "PhoneNumberSet"   => $PN,
                "TemplateParamSet" => $Param,
            ]);
            if ($result['code'] !== 0) {
                if ($result['SendStatusSet'][0]['SerialNo']) {
                    $result = [
                        "code" => 1,
                        "msg"  => "发送成功",
                        "data" => $PN,
                    ];
                } else {
                    $result = [
                        "code" => 0,
                        "msg"  => $result['SendStatusSet'][0]['Message'],
                    ];
                }
            }
        } else {
            $result = [
                "code" => 0,
                "msg"  => "手机号不能为空",
            ];
        }
        return $result;
    }
}
