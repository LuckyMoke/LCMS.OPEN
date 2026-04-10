<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2026-04-07 11:29:59
 * @Description:前台基类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('common');
class webbase extends common
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
        $this->load_web_config();
        $this->load_web_url();
        $this->check_level();
        $this->load_tpl_config();
    }
    public function load_web_config()
    {
        global $_L;
        $_L['ROOTID'] = $_L['form']['rootid'] ?: 0;
        $_L['ROOTID'] = intval($_L['ROOTID']);
        $webcfg       = array_filter(LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "web",
            "lcms" => $_L['ROOTID'],
        ]));
        $_L['config']['web'] = $webcfg ? array_merge($_L['config']['web'], $webcfg) : $_L['config']['web'];
    }
    public function load_web_url($host = "", $scheme = "")
    {
        global $_L;
        $host      = $host ?: HTTP_HOST;
        $host      = realhost($host);
        $scheme    = $scheme ?: (getscheme() ? "https://" : "http://");
        $url_site  = "{$scheme}{$host}/";
        $rootsid   = $_L['form']['rootsid'] ? "rootsid={$_L['form']['rootsid']}&" : "";
        $url_own   = "{$url_site}app/index.php?rootid={$_L['ROOTID']}&{$rootsid}";
        $_L['url'] = [
            "scheme" => $scheme,
            "site"   => $url_site,
            "now"    => "{$scheme}{$host}" . HTTP_URI,
            "public" => "{$url_site}public/",
            "static" => "{$url_site}public/static/",
            "upload" => "{$url_site}upload/",
            "cache" => "{$url_site}cache/",
            "app" => "{$url_site}app/",
            "captcha" => "{$url_site}quick/captcha.php?{$rootsid}",
            "altcha" => "{$url_site}quick/altcha.php?action=",
            "qrcode" => "{$url_site}quick/qrcode.php?token=" . ssl_encode_gzip(time() + 86400, "qrcode") . "&text=",
            "own" => "{$url_own}",
            "own_path" => "{$url_site}app/" . L_TYPE . "/" . L_NAME . "/",
            "own_form" => "{$url_own}n=" . L_NAME . "&c=" . L_CLASS . "&a=",
        ];
        // 系统URL参数
        $host     = realhost($_L['config']['web']['domain']);
        $scheme   = $_L['config']['web']['https'] == 1 ? "https://" : "http://";
        $url_site = "{$scheme}{$host}/";

        $_L['url']['sys'] = [
            "scheme" => $scheme,
            "domain" => $host,
            "site"   => $url_site,
            "api"    => $_L['config']['web']['domain_api'],
            "app"    => "{$url_site}app/",
            "own" => "{$url_site}app/index.php?rootid={$_L['ROOTID']}&{$rootsid}",
        ];
        $this->load_plugin_info();
    }
    public function check_level()
    {
        global $_L;
        if ($_L['ROOTID'] > 0) {
            $admininfo = sql_get(["admin", "id = '{$_L['ROOTID']}'"]);
            if ($admininfo['status'] == "1") {
                if ($admininfo['lasttime'] > "0000-00-00 00:00:00" && $admininfo['lasttime'] < datenow()) {
                    LCMS::X(403, "账户已到期，请联系管理员");
                }
            } else {
                LCMS::X(403, "账户已停用，请联系管理员");
            }
        }
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
        $plugin['oss']['type'] = $plugin['oss']['type'] ?: "local";
        if ($plugin['oss']['type'] != "local") {
            if ($plugin['oss']['super'] == "1") {
                $_L['plugin']['oss'] = $plugin['oss'];
                return;
            }
        }
        $_L['plugin']['oss']['type'] = $_L['plugin']['oss']['type'] ?: "local";
    }
    public function load_tpl_config($tpl = "")
    {
        global $_L;
        $config = PATH_APP_NOW . "web/tpl/{$tpl}/config.php";
        if (is_file($config)) {
            LOAD::sys_class("tpl");
            TPL::$tplpath = $tpl;
            require_once $config;
            TPL::init($paths);
        }
    }
    public function domain($domain = "", $prefix = false)
    {
        global $_L;
        if (is_url($domain)) {
            $domain = parse_url($domain);
            $scheme = $domain['scheme'] == "https" ? "https://" : "http://";
            $host   = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
        } else {
            $host = trim($domain, "/ ");
        }
        if ($host && $prefix) {
            $host = substr(md5($_L['ROOTID'] + L_NAME + L_CLASS + L_ACTION), 8, 16) . "." . $host;
        };
        $this->load_web_url($host, $scheme);
    }
}
