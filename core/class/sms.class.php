<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-07-26 17:56:47
 * @LastEditTime: 2023-08-23 18:14:39
 * @Description: 短信发送类
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SMS
{
    public static function init($config = [])
    {
        global $_L;
        $config = $config ?: LCMS::config([
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
        ])['sms'];
        $type = $config['type'];
        if (!$type) {
            return [
                "code" => 0,
                "msg"  => "未配置短信接口",
            ];
        }
        return [
            "type" => $type,
            "cfg"  => $config[$type],
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
        $cfg  = array_merge($init['cfg'], $Param);
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
