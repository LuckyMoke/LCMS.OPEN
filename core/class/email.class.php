<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-02-27 14:48:35
 * @Description:邮件发送类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EMAIL
{
    /**
     * @description: 初始化邮件配置
     * @param array $config
     * @return array
     */
    public static function init($config = [])
    {
        global $_L;
        $config = $config ?: LCMS::config([
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
        ])['email'];
        $type = $config['type'];
        return [
            "type" => $type,
            "cfg"  => $config[$type],
        ];
    }
    /**
     * @description: 发送邮件
     * @param array $Param [TO, Title, Body]
     * @param array $config
     * @return array
     */
    public static function send($Param = [], $config = [])
    {
        global $_L;
        $init = self::init($config);
        $cfg  = array_merge($init['cfg'], $Param);
        switch ($init['type']) {
            case 'smtp':
                load::plugin("PHPMailer/Exception");
                load::plugin("PHPMailer/PHPMailer");
                load::plugin("PHPMailer/SMTP");
                $email = new PHPMailer(true);
                $email->setLanguage("zh_cn", PATH_CORE_PLUGIN . "PHPMailer/language/");
                $email->SMTPDebug = 0;
                $email->isSMTP();
                $email->Host       = $cfg['Smtp'];
                $email->SMTPAuth   = true;
                $email->Username   = $cfg['From'];
                $email->Password   = $cfg['Pass'];
                $email->SMTPSecure = $cfg['SSL'] ? "ssl" : "tls";
                $email->Port       = $cfg['Port'];
                $email->Timeout    = 30;
                $EM                = [];
                if (is_array($Param['TO'])) {
                    foreach ($Param['TO'] as $val) {
                        if ($val && is_email($val)) {
                            $PN[] = $val;
                        }
                    }
                } elseif (is_email($Param['TO'])) {
                    $EM[] = $Param['TO'];
                }
                if ($EM) {
                    $email->setFrom($cfg['From'], $cfg['Alias']);
                    foreach ($EM as $val) {
                        $email->addAddress($val);
                    }
                    if ($cfg['Reply']) {
                        $email->AddReplyTo($cfg['Reply']);
                    }
                    if ($Param['Att']) {
                        foreach ($Param['Att'] as $val) {
                            $email->addAttachment($val['path'], $val['title']);
                        }
                    }
                    $email->isHTML(true);
                    $email->Subject = $Param['Title'];
                    $email->Body    = $Param['Body'];
                    try {

                        $email->send();
                        $result = [
                            "code" => 1,
                            "msg"  => "发送成功",
                        ];
                    } catch (Exception $e) {
                        $result = [
                            "code" => 0,
                            "msg"  => $email->ErrorInfo,
                        ];
                    }
                } else {
                    $result = [
                        "code" => 0,
                        "msg"  => "收件地址不能为空",
                    ];
                }
                break;
            case 'aliyun':
                load::plugin('Aliyun/AliyunEMAIL');
                $EMAIL = new AliyunEMAIL([
                    "AccessKeyId"     => $cfg['AccessKeyId'],
                    "AccessKeySecret" => $cfg['AccessKeySecret'],
                    "From"            => $cfg['From'],
                    "Reply"           => $cfg['Reply'],
                    "Alias"           => $cfg['Alias'],
                ]);
                $result = $EMAIL->send($Param['TO'], $Param['Title'], $Param['Body']);
                break;
            case 'tencent':
                load::plugin('Tencent/TencentEMAIL');
                $EMAIL = new TencentEMAIL([
                    "secretId"  => $cfg['secretId'],
                    "secretkey" => $cfg['secretkey'],
                    "From"      => $cfg['From'],
                    "Reply"     => $cfg['Reply'],
                    "Alias"     => $cfg['Alias'],
                ]);
                $result = $EMAIL->send($Param['TO'], $Param['Title'], $Param['Body']);
                break;
        }
        LCMS::log([
            "type"     => "email",
            "info"     => "{$Param['TO']}-{$result['msg']}",
            "postdata" => [
                "title" => $Param['Title'],
                "body"  => $Param['Body'],
            ],
        ]);
        return $result ?: [];
    }
}
