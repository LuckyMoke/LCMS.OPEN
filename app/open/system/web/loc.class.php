<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
class loc extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        if (!$_L['form']['key']) {
            LCMS::X(403, "缺少KEY");
        } else {
            $key      = $_L['form']['key'];
            $info     = $_L['form']['info'] ? $_L['form']['info'] : "请授权获取定位信息，以便提供更好的服务！";
            $goback   = $_L['form']['goback'];
            $callback = $_L['form']['callback'];
        }
        require LCMS::template("own/loc/index");
    }
    public function doiploc()
    {
        global $_L;
        if (!$_L['form']['key']) {
            LCMS::X(403, "缺少KEY");
        } else {
            $url = "https://apis.map.qq.com/ws/location/v1/ip?key={$_L['form']['key']}" . "&ip=" . CLIENT_IP;
            $loc = json_decode(http::get($url), true);
            $loc = $loc['result']['ad_info'];
            if ($loc['province'] && $loc['city'] && $loc['district']) {
                $loc['area'] = $loc['district'];
                ajaxout(1, "success", "", $loc);
            } else {
                ajaxout(0, '定位获取失败');
            }
        }
    }
    public function docode()
    {
        global $_L;
        if ($_L['form']['goback']) {
            $domain = parse_url($_L['form']['goback']);
            $loc    = $_L['form']['loc'];
            if ($loc['province'] && $loc['city'] && $loc['district']) {
                $result = json_encode([
                    "code" => 1,
                    "msg"  => "success",
                    "loc"  => $loc,
                ]);
            } else {
                $result = json_encode([
                    "code" => 0,
                    "msg"  => "定位获取失败",
                ]);
            }
            ajaxout(1, "success", "", "{$domain['scheme']}://{$domain['host']}/app/index.php?n=system&c=loc&a=set_cookie&code=" . base64_encode($result) . "&goback=" . urlencode($_L['form']['goback']));
        }
    }
    public function doset_cookie()
    {
        global $_L;
        if ($_L['form']['code'] && $_L['form']['goback']) {
            $result = json_decode(base64_decode($_L['form']['code']), true);
            if ($result['msg']) {
                setcookie("LCMSLOCTION", base64_decode($_L['form']['code']), 0, "/", "", 0);
                goheader($_L['form']['goback']);
            }
        }
        LCMS::X(500, "数据错误");
    }
}
