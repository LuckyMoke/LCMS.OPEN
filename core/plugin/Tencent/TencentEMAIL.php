<?php
require "libs/Tencent.Api.php";
class TencentEMAIL
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
            "Host"      => "ses.tencentcloudapi.com",
            "Action"    => "SendEmail",
            "Version"   => "2020-10-02",
            "Region"    => "ap-hongkong",
        ]);
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
            $DATA = [
                "FromEmailAddress" => $this->cfg['From'],
                "ReplyToAddresses" => $this->cfg['Alias'] ? "{$this->cfg['Alias']}<{$this->cfg['Reply']}>" : $this->cfg['Reply'],
                "Subject"          => $Title,
                "Destination"      => $EM,
                "Attachments"      => $Att,
            ];
            if (is_array($Body)) {
                $DATA = array_merge($DATA, [
                    "Template" => [
                        "TemplateID"   => $Body['ID'],
                        "TemplateData" => json_encode($Body['Param']),
                    ],
                ]);
            } else {
                $DATA = array_merge($DATA, [
                    "Simple" => [
                        "Html" => base64_encode($Body),
                    ],
                ]);
            }
            $result = $this->Api->reQuest("POST", $DATA);
            if ($result['code'] !== 0) {
                $result = [
                    "code" => 1,
                    "msg"  => "发送成功",
                    "data" => $EM,
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
}
