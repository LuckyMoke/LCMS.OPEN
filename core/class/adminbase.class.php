<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-05-29 15:17:21
 * @Description:后台基类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('common');
load::sys_class('layui');
load::sys_class('level');
class adminbase extends common
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
        $this->load_admin_url();
        $this->check_login();
        $this->load_web_url();
        $this->load_app_info();
        $this->load_plugin_info();
        $this->check_level();
    }
    protected function load_admin_url()
    {
        global $_L;
        switch ($_L['config']['admin']['https']) {
            case 1:
                $scheme = "https://";
                break;
            default:
                $scheme = getscheme() ? "https://" : "http://";
                break;
        }
        $url_site  = $scheme . HTTP_HOST . "/";
        $url_now   = $scheme . HTTP_HOST . HTTP_URI;
        $url_admin = $url_site . ($_L['config']['admin']['dir'] ?: "admin") . "/";
        $rootsid   = $_L['form']['rootsid'] ? "rootsid={$_L['form']['rootsid']}&" : "";
        $_L['url'] = [
            "scheme"   => $scheme,
            "site"     => $url_site,
            "now"      => $url_now,
            "admin"    => $url_admin,
            "public"   => "{$url_site}public/",
            "static"   => "{$url_site}public/static/",
            "upload"   => "{$url_site}upload/",
            "cache"    => "{$url_site}cache/",
            "app"      => "{$url_site}app/",
            "captcha"  => "{$url_site}quick/captcha.php?{$rootsid}",
            "qrcode"   => "{$url_site}quick/qrcode.php?text=",
            "own"      => "{$url_admin}index.php?{$rootsid}",
            "own_path" => "{$url_site}app/" . L_TYPE . "/" . L_NAME . "/",
            "own_form" => "{$url_admin}index.php?{$rootsid}t=" . L_TYPE . "&n=" . L_NAME . "&c=" . L_CLASS . "&a=",
        ];
    }
    protected function check_login()
    {
        global $_L;
        $_L['LCMSADMIN'] = SESSION::get("LCMSADMIN");
        $loginrootid     = SESSION::get("LOGINROOTID");
        $loginrootid     = $loginrootid ?: $_L['form']['rootid'] ?: 0;
        $loginurl        = "{$_L['url']['admin']}index.php?rootid={$loginrootid}&n=login";
        $okinfourl       = okinfo($loginurl, 0, "top", true);
        if ($_L['LCMSADMIN']) {
            $admininfo = sql_get(["admin", "id = '{$_L['LCMSADMIN']['id']}'"]);
            if ($_L['config']['admin']['login_limit'] != "1" && $admininfo['logintime'] != $_L['LCMSADMIN']['logintime'] && !$_L['LCMSADMIN']['god']) {
                SESSION::del("LCMSADMIN");
                LCMS::X(403, "已在其它地方登陆账号，此设备自动退出", $okinfourl);
            }
            if ($admininfo['type'] != $_L['LCMSADMIN']['type']) {
                SESSION::del("LCMSADMIN");
                LCMS::X(403, "系统权限已修改，请重新登陆", $okinfourl);
            }
            if ($admininfo['status'] != 1) {
                SESSION::del("LCMSADMIN");
                LCMS::X(403, "此账号已禁用，请联系客服", $okinfourl);
            }
            if ($_L['LCMSADMIN']['type'] != "lcms") {
                $level = sql2arr($_L['LCMSADMIN']['parameter'])['level'];
                if (!$level) {
                    $level = sql_get([
                        "table" => "admin_level",
                        "where" => "id = {$_L['LCMSADMIN']['type']}",
                    ]);
                    $level = sql2arr($level['parameter']);
                }
                $_L['LCMSADMIN']['level'] = $level;
            }
            if ($_L['LCMSADMIN']['tuid']) {
                $_L['LCMSADMIN']['lcms'] = sql_get([
                    "table" => "admin",
                    "where" => "id = {$_L['LCMSADMIN']['tuid']}",
                ])['lcms'];
            }
            $_L['ROOTID'] = isset($_L['LCMSADMIN']['lcms']) && $_L['LCMSADMIN']['lcms'] == 0 ? $_L['LCMSADMIN']['id'] : $_L['LCMSADMIN']['lcms'];
            $_L['ROOTID'] = LCMS::SUPER() ? 0 : $_L['ROOTID'];
        } else {
            if (PHP_SELF == HTTP_URI . "index.php") {
                okinfo($loginurl);
            }
            if (!in_string(HTTP_URI, "n=login")) {
                LCMS::X(403, "请重新登录", $okinfourl);
            }
        }
        $webcfg = array_filter(LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "web",
            "lcms" => $_L['ROOTID'],
        ]));
        $_L['config']['web'] = $webcfg ? array_merge($_L['config']['web'], $webcfg) : $_L['config']['web'];
    }
    protected function load_web_url($domain = "", $scheme = "")
    {
        global $_L;
        $domain           = $domain ?: ($_L['config']['web']['domain'] ?: HTTP_HOST);
        $scheme           = $scheme ?: ($_L['config']['web']['https'] == 1 ? "https://" : "http://");
        $url_site         = "{$scheme}{$domain}/";
        $rootsid          = $_L['form']['rootsid'] ? "rootsid={$_L['form']['rootsid']}&" : "";
        $_L['url']['web'] = [
            "scheme"   => $scheme,
            "site"     => $url_site,
            "api"      => $_L['config']['web']['domain_api'],
            "public"   => "{$url_site}public/",
            "static"   => "{$url_site}public/static/",
            "upload"   => "{$url_site}upload/",
            "cache"    => "{$url_site}cache/",
            "app"      => "{$url_site}app/",
            "own"      => "{$url_site}app/index.php?rootid={$_L['ROOTID']}&{$rootsid}",
            "own_path" => "{$url_site}app/" . L_TYPE . "/" . L_NAME . "/",
        ];
    }
    protected function load_app_info()
    {
        global $_L;
        $_L['APP'] = LEVEL::app();
    }
    protected function load_plugin_info()
    {
        global $_L;
        $plugin = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "plugin",
            "lcms" => true,
        ]);
        $_L['plugin'] = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "plugin",
        ]);
        if (!$_L['plugin']['aimodel']['type'] && $plugin['aimodel']['type']) {
            $_L['plugin']['aimodel'] = $plugin['aimodel'];
        }
        $plugin['oss']['type'] = $plugin['oss']['type'] ?: "local";
        if ($plugin['oss']['type'] != "local" && $plugin['oss']['super'] > 0) {
            $_L['plugin']['oss'] = $plugin['oss'];
            return;
        }
        if (!LCMS::SUPER() && $plugin['oss']['must'] > 0) {
            $_L['plugin']['oss']['type'] = $_L['plugin']['oss']['type'] ?: "qiniu";
            return;
        }
        $_L['plugin']['oss']['type'] = $_L['plugin']['oss']['type'] ?: "local";
    }
    protected function check_level()
    {
        global $_L;
        if (L_NAME === "appstore" && L_CLASS === "store" && ($_L['form']['action'] === "content" || $_L['form']['apply'])) {
            return;
        }
        $fun = str_replace("do", "", L_ACTION);
        if ($_L['APP']['power'][L_CLASS][$fun]) {
            LCMS::X(403, "没有权限，禁止访问");
        }
    }
    public function domain($domain = "", $scheme = "", $autodomain = false)
    {
        global $_L;
        if (is_url($domain)) {
            $domain = parse_url($domain);
            $scheme = $domain['scheme'] === "https" ? "https://" : "http://";
            $domain = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
        }
        if ($domain && $autodomain) {
            $domain = substr(md5($_L['ROOTID'] + L_NAME + L_CLASS + L_ACTION), 8, 16) . "." . $domain;
        };
        $this->load_web_url($domain, $scheme);
    }
}
