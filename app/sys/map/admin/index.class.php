<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-11-19 12:17:09
 * @LastEditTime: 2024-10-24 16:42:51
 * @Description: 地图选择器
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC, $PLG;
        parent::__construct();
        $LF  = $_L['form'];
        $LC  = $LF['LC'];
        $PLG = LCMS::config([
            "do"   => "get",
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
            "lcms" => true,
        ]);
        $PLG = $PLG['map'] ?: [];
    }
    public function dotianditu()
    {
        global $_L, $LF, $LC, $PLG;
        if (
            !$PLG['tianditu'] ||
            !$PLG['tianditu']['key']
        ) {
            okinfo("{$_L['url']['own_form']}api");
        }
        require LCMS::template("own/tianditu");
    }
    public function doapi()
    {
        global $_L, $LF, $LC, $PLG;
        switch ($LF['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "name" => "config",
                    "cate" => "plugin",
                    "lcms" => true,
                    "form" => [
                        "map" => $LC,
                    ],
                ]);
                ajaxout(1, "保存成功", "{$_L['url']['own_form']}tianditu");
                break;
            case 'get':
                if (
                    $PLG['tianditu'] &&
                    $PLG['tianditu']['key']
                ) {
                    ajaxout(1, "success", "", $PLG['tianditu']['key']);
                }
                ajaxout(0, "error");
                break;
            default:
                $form = [
                    ["layui" => "des", "title" => "由于百度、腾讯、高德地图商业使用需支付每年5万元以上的费用，故使用了目前还免费的天地图！"],
                    ["layui" => "des", "title" => "天地图注册地址 <a href=\"http://lbs.tianditu.gov.cn/\" target=\"_blank\">http://lbs.tianditu.gov.cn/</a><br>创建应用类型：<code>浏览器端</code><br>域名白名单：<code>填你网站实际使用的域名</code>"],
                    ["layui" => "input", "title" => "天地图Key",
                        "name"   => "LC[tianditu][key]",
                        "value"  => $PLG['tianditu']['key'],
                        "verify" => "required"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/api");
                break;
        }
    }
}
