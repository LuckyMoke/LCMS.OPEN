<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-07-26 17:56:47
 * @LastEditTime: 2025-06-05 20:31:08
 * @Description: 短信发送类
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SMS
{
    /**
     * @description: 初始化短信配置
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
            ])['sms'];
        }
        $config = $config ?: [];
        switch ($config['type']) {
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
                    $config['tencent']['secretkey'] &&
                    $config['tencent']['SmsSdkAppId']
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
            if ($config && $config['sms']) {
                return self::init(array_merge($config['sms'], [
                    "lcms" => true,
                ]));
            }
        }
        return [
            "code" => 0,
            "msg"  => "未配置短信接口",
        ];
    }
    /**
     * @description: 发送短信
     * @param array $Param [ID, Name, Phone, Param]
     * @param array $config
     * @return array
     */
    public static function send($Param = [], $config = [])
    {
        global $_L;
        $init = self::init($config);
        if (!$init['cfg']) return $init;
        $cfg = array_merge($init['cfg'], $Param);
        switch ($init['type']) {
            case 'aliyun':
                load::plugin('Aliyun/AliyunSMS');
                $SMS = new AliyunSMS([
                    "AccessKeyId"     => $cfg['AccessKeyId'],
                    "AccessKeySecret" => $cfg['AccessKeySecret'],
                    "TemplateCode"    => $Param['ID'],
                    "SignName"        => $Param['Name'],
                ]);
                $result = $SMS->send($Param['Phone'], $Param['Param']);
                break;
            case 'tencent':
                load::plugin('Tencent/TencentSMS');
                $SMS = new TencentSMS([
                    "secretId"    => $cfg['secretId'],
                    "secretkey"   => $cfg['secretkey'],
                    "SmsSdkAppId" => $cfg['SmsSdkAppId'],
                    "TemplateId"  => $Param['ID'],
                    "SignName"    => $Param['Name'],
                ]);
                $result = $SMS->send($Param['Phone'], array_values($Param['Param']));
                break;
        }
        LCMS::log([
            "type"     => "sms",
            "info"     => "{$Param['Phone']}-{$result['msg']}",
            "postdata" => $Param['Param'],
        ]);
        return $result ?: [];
    }
}
