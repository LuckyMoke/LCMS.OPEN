<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-06-05 20:32:15
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
        if (!$config) {
            $config = LCMS::config([
                "type" => "sys",
                "name" => "config",
                "cate" => "plugin",
            ])['email'];
        }
        $config = $config ?: [];
        switch ($config['type']) {
            case 'smtp':
                if (
                    $config['smtp']['Smtp'] &&
                    $config['smtp']['Pass']
                ) {
                    return [
                        "type" => "smtp",
                        "cfg"  => $config['smtp'],
                    ];
                }
                break;
            case 'aliyun':
                if (
                    $config['aliyun']['AccessKeyId'] &&
                    $config['aliyun']['AccessKeySecret']
                ) {
                    return [
                        "type" => "aliyun",
                        "cfg"  => $config['aliyun'],
                    ];
                }
                break;
            case 'tencent':
                if (
                    $config['tencent']['secretId'] &&
                    $config['tencent']['secretkey']
                ) {
                    return [
                        "type" => "tencent",
                        "cfg"  => $config['tencent'],
                    ];
                }
                break;
        }
        if (
            !$config['lcms'] &&
            !LCMS::SUPER() &&
            $_L['LCMSADMIN'] &&
            $_L['LCMSADMIN']['lcms'] == 0
        ) {
            $config = LCMS::config([
                "type" => "sys",
                "name" => "config",
                "cate" => "plugin",
                "lcms" => true,
            ]);
            if ($config && $config['email']) {
                return self::init(array_merge($config['email'], [
                    "lcms" => true,
                ]));
            }
        }
        return [
            "code" => 0,
            "msg"  => "未配置邮箱接口",
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
        if (!$init['cfg']) {
            return $init;
        }
        $cfg = array_merge($init['cfg'], $Param);
        switch ($init['type']) {
            case 'smtp':
                LOAD::plugin("PHPMailer/Exception");
                LOAD::plugin("PHPMailer/PHPMailer");
                LOAD::plugin("PHPMailer/SMTP");
                $EMAIL = new PHPMailer(true);
                $EMAIL->setLanguage("zh_cn", PATH_CORE_PLUGIN . "PHPMailer/language/");
                $EMAIL->SMTPDebug = 0;
                $EMAIL->isSMTP();
                $EMAIL->Host       = $cfg['Smtp'];
                $EMAIL->SMTPAuth   = true;
                $EMAIL->Username   = $cfg['From'];
                $EMAIL->Password   = $cfg['Pass'];
                $EMAIL->SMTPSecure = $cfg['SSL'] ? "ssl" : "tls";
                $EMAIL->Port       = $cfg['Port'];
                $EMAIL->Timeout    = 30;
                $EMAIL->CharSet    = "utf-8";
                $EM                = [];
                if (is_array($Param['TO'])) {
                    foreach ($Param['TO'] as $val) {
                        $val = trim($val);
                        if ($val && is_email($val)) {
                            $PN[] = $val;
                        }
                    }
                } else {
                    $Param['TO'] = trim($Param['TO']);
                    if (is_email($Param['TO'])) {
                        $EM[] = $Param['TO'];
                    }
                }
                if ($EM) {
                    $EMAIL->setFrom($cfg['From'], $cfg['Alias']);
                    foreach ($EM as $val) {
                        $EMAIL->addAddress($val);
                    }
                    if ($cfg['Reply']) {
                        $EMAIL->AddReplyTo($cfg['Reply']);
                    }
                    if ($Param['Att']) {
                        foreach ($Param['Att'] as $val) {
                            $EMAIL->addAttachment($val['path'], $val['title']);
                        }
                    }
                    $EMAIL->isHTML(true);
                    $EMAIL->Subject = $Param['Title'];
                    $EMAIL->Body    = $Param['Body'];
                    try {
                        $EMAIL->send();
                        $result = [
                            "code" => 1,
                            "msg"  => "发送成功",
                        ];
                    } catch (Exception $e) {
                        $errmsg = mb_convert_encoding($EMAIL->ErrorInfo, "UTF-8");
                        $result = [
                            "code" => 0,
                            "msg"  => $errmsg,
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
                LOAD::plugin('Aliyun/AliyunEMAIL');
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
                LOAD::plugin('Tencent/TencentEMAIL');
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
