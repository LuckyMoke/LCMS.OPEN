<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2024-05-27 11:11:38
 * @LastEditTime: 2025-02-12 16:58:38
 * @Description: AI大模型
 * Copyright 2024 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/index");
    }
    public function doconfig()
    {
        global $_L, $LF, $LC;
        $aicfg = $_L['plugin']['aimodel'];
        $PLG   = $aicfg[$aicfg['type']];
        switch ($aicfg['type']) {
            case 'wenxin':
                $cname = md5(json_encode($PLG));
                $token = LCMS::cache($cname);
                if ($token['expires'] < time()) {
                    LOAD::plugin("Baidu/libs/Baidu.Api");
                    $BD = new BaiduApi([
                        "AccessKey" => $PLG['access_key'],
                        "SecretKey" => $PLG['secret_key'],
                    ]);
                    $iam    = "https://iam.bj.baidubce.com/v1/BCE-BEARER/token?expireInSeconds=86400";
                    $sign   = $BD->sign("GET", $iam);
                    $result = HTTP::request([
                        "type"    => "GET",
                        "url"     => $iam,
                        "headers" => [
                            "Content-Type"  => "application/json",
                            "Authorization" => $sign['sign'],
                        ],
                    ]);
                    $result = json_decode($result, true);
                    if ($result['token']) {
                        $token = [
                            "token"   => $result['token'],
                            "expires" => time() + 86000,
                        ];
                        LCMS::cache($cname, $token);
                    } else {
                        ajaxout(0, "获取BearerToken失败");
                    }
                }
                $result = [
                    "api"   => "https://qianfan.baidubce.com/v2/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $token['token'],
                ];
                break;
            case 'spark':
                $result = [
                    "api"       => "spark-api.xf-yun.com",
                    "model"     => $PLG['model'],
                    "appid"     => $PLG['appid'],
                    "apisecret" => $PLG['apisecret'],
                    "apikey"    => $PLG['apikey'],
                ];
                break;
            case 'qianwen':
                $result = [
                    "api"   => "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'baichuan':
                $result = [
                    "api"   => "https://api.baichuan-ai.com/v1/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'zhipu':
                $result = [
                    "api"   => "https://open.bigmodel.cn/api/paas/v4/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'kimi':
                $result = [
                    "api"   => "https://api.moonshot.cn/v1/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'siliconcloud':
                $result = [
                    "api"   => "https://api.siliconflow.cn/v1/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'deepseek':
                $result = [
                    "api"   => "https://api.deepseek.com/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'openai':
                switch ($PLG['type']) {
                    case 'api2d':
                        $api = "https://oa.api2d.net";
                        break;
                    case 'openai':
                        $api = "https://api.openai.com";
                        break;
                }
                $api = $PLG['api'] ?: $api;
                ajaxout(1, "success", "", [
                    "type"  => $PLG['type'],
                    "api"   => $api,
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ]);
                break;
            default:
                ajaxout(0, "AI助手未开启，请到设置->接口设置->AI接口中开启！");
                break;
        }
        $result['max_tokens'] = intval($aicfg['max_tokens']);
        ajaxout(1, "success", "", $result);
    }
}
