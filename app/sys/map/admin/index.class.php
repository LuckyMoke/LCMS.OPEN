<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-11-19 12:17:09
 * @LastEditTime: 2025-01-03 15:34:54
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
                    ["layui" => "des", "title" => "由于百度、腾讯、高德地图商业使用需支付每年5万元以上的费用，故使用了由国家基础地理信息中心提供的天地图！"],
                    ["layui" => "des", "title" => "▲ 注意：天地图仅支持企业信息注册，并通过企业认证后才能使用。<br>天地图注册地址 <a href=\"http://lbs.tianditu.gov.cn/\" target=\"_blank\">http://lbs.tianditu.gov.cn/</a><br>创建应用类型：<code>浏览器端</code><br>域名白名单：<code>填你网站实际使用的域名</code>"],
                    ["layui" => "des", "title" => "也可以自己制作地图图片，或去天地图官网截图，上传到网站使用。<br>天地图在线地图：<a href=\"https://map.tianditu.gov.cn/\" target=\"_blank\">https://map.tianditu.gov.cn/</a><br>▲ 注意：使用天地图截图或作图使用，需在图片上注明来源“天地图”。<br>天地图版权声明：<a href=\"https://www.tianditu.gov.cn/about/\" target=\"_blank\">https://www.tianditu.gov.cn/about/</a>"],
                    ["layui" => "input", "title" => "天地图Key",
                        "name"   => "LC[tianditu][key]",
                        "value"  => $PLG['tianditu']['key']],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/api");
                break;
        }
    }
}
