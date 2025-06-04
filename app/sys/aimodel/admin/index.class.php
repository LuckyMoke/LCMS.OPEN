<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2024-05-27 11:11:38
 * @LastEditTime: 2025-05-25 10:07:39
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
                $result = [
                    "api"   => "https://qianfan.baidubce.com/v2/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'aliyun':
                $result = [
                    "api"   => "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions",
                    "model" => $PLG['model'],
                    "token" => $PLG['token'],
                ];
                break;
            case 'huoshan':
                $result = [
                    "api"   => "https://ark.cn-beijing.volces.com/api/v3/chat/completions",
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
