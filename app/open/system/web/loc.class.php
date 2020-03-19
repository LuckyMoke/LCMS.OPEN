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
    public function docode()
    {
        global $_L;
        if ($_L['form']['goback']) {
            $domain = parse_url($_L['form']['goback']);
            $result = $_L['form']['loc'];
            $code   = ssl_encode(json_encode([
                "code" => 1,
                "loc"  => $result,
            ]));
            ajaxout(1, "success", "", "{$domain['scheme']}://{$domain['host']}/app/index.php?n=system&c=loc&a=set_cookie&code={$code}&goback=" . urlencode($_L['form']['goback']));
        }
    }
    public function doset_cookie()
    {
        global $_L;
        if ($_L['form']['code'] && $_L['form']['goback']) {
            $result = json_decode(ssl_decode($_L['form']['code']), true);
            if ($result['code'] === 1) {
                setcookie("LCMSLOCTION", json_encode_ex($result['loc']), 0, "/", "", 0);
                okinfo($_L['form']['goback']);
            }
        } else {
            LCMS::X(500, "数据错误");
        }
    }
}
