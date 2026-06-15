<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2024-05-27 11:11:38
 * @LastEditTime: 2026-06-14 23:24:36
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
        if ($aicfg['on'] != 1) $this->ajaxerror();
        $maxtoken = 2048;
        $thinking = false;
        switch ($LF['action']) {
            case 'chat':
                $plg   = $aicfg[$aicfg['chat']];
                $model = $plg['model'];
                $model = explode("|", $model);
                if ($model[1] && $model[1] == "thinking") {
                    $plg['model'] = $model[0];
                    $thinking     = true;
                }
                $maxtoken = $aicfg['max_tokens'];
                switch ($aicfg['chat']) {
                    case 'wenxin':
                        $result = [
                            "name"  => "wenxin",
                            "api"   => "https://qianfan.baidubce.com/v2/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'aliyun':
                        $result = [
                            "name"  => "aliyun",
                            "api"   => "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'huoshan':
                        $result = [
                            "name"  => "huoshan",
                            "api"   => "https://ark.cn-beijing.volces.com/api/v3/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'siliconcloud':
                        $result = [
                            "name"  => "siliconcloud",
                            "api"   => "https://api.siliconflow.cn/v1/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'deepseek':
                        $result = [
                            "name"  => "deepseek",
                            "api"   => "https://api.deepseek.com/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'openai':
                        switch ($plg['type']) {
                            case 'api2d':
                                $api = "https://oa.api2d.net";
                                break;
                            case 'openai':
                                $api = "https://api.openai.com";
                                break;
                        }
                        $result = [
                            "name"  => "openai",
                            "type"  => $plg['type'],
                            "api"   => $plg['api'] ?: $api,
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    default:
                        $this->ajaxerror();
                        break;
                }
                break;
            case 'code':
                $plg = $aicfg['code'];
                if (!$plg['token']) $this->ajaxerror();
                $maxtoken = 65536;
                $result   = [
                    "name"  => "code",
                    "api"   => "https://open.bigmodel.cn/api/paas/v4/chat/completions",
                    "model" => "glm-5.1",
                    "token" => $plg['token'],
                ];
                break;
        }
        $result['max_tokens'] = intval($maxtoken);
        $result['thinking']   = $thinking;
        ajaxout(1, "success", "", $result);
    }
    /**
     * @description: 输出错误信息
     * @return {*}
     */
    private function ajaxerror()
    {
        ajaxout(0, "AI接口未配置，请到设置->接口设置->AI接口中配置！");
    }
}
