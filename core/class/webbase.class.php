<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-01-29 14:46:32
 * @Description:前台基类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('common');
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
        $_L['ROOTID'] = $_L['form']['rootid'] ?: "0";
    }
    public function load_web_url($domain = "", $secure = "")
    {
        global $_L;
        if ($_L['config']['web']['domain'] && $_L['config']['web']['domain_must'] && stristr(HTTP_HOST, $_L['config']['web']['domain']) === false) {
            LCMS::X(403, "请通过正确域名访问");
        }
        // 当前域名数据
        $domain    = $domain ?: (HTTP_HOST ?: $_L['config']['web']['domain']);
        $secure    = $secure ?: ($_L['config']['web']['https'] == "1" ? "https://" : "http://");
        $url_site  = "{$secure}{$domain}/";
        $url_own   = "{$url_site}app/index.php?rootid={$_L['ROOTID']}&";
        $_L['url'] = [
            "secure"   => $secure,
            "site"     => $url_site,
            "now"      => "{$secure}{$domain}" . HTTP_QUERY,
            "public"   => "{$url_site}public/",
            "static"   => "{$url_site}public/static/",
            "upload"   => "{$url_site}upload/",
            "cache"    => "{$url_site}cache/",
            "app"      => "{$url_site}app/",
            "qrcode"   => "{$url_site}app/index.php?n=system&c=qr&text=",
            "own"      => "{$url_own}",
            "own_path" => "{$url_site}app/" . L_TYPE . "/" . L_NAME . "/",
            "own_form" => "{$url_own}n=" . L_NAME . "&c=" . L_CLASS . "&a=",
        ];
        // 系统URL参数
        $secure   = $_L['config']['web']['https'] == "1" ? "https://" : "http://";
        $url_site = "{$secure}{$_L['config']['web']['domain']}/";

        $_L['url']['sys'] = [
            "secure" => $secure,
            "domain" => $_L['config']['web']['domain'],
            "site"   => $url_site,
            "api"    => $_L['config']['web']['domain_api'],
            "app"    => "{$url_site}app/",
            "own"    => "{$url_site}app/index.php?rootid={$_L['ROOTID']}&",
        ];
    }
    public function check_level()
    {
        global $_L;
        if ($_L['ROOTID'] > "0") {
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
    public function load_tpl_config($tpl = "")
    {
        global $_L;
        $config = PATH_APP_NOW . "web/tpl/{$tpl}/config.php";
        if (is_file($config)) {
            load::sys_class("tpl");
            TPL::$tplpath = $tpl;
            require_once $config;
            TPL::getui($paths);
        }
    }
    public function domain($domain = "", $secure = "", $autodomain = false)
    {
        global $_L;
        if (is_url($domain)) {
            $domain = parse_url($domain);
            $secure = $domain['scheme'] == "https" ? "https://" : "http://";
            $domain = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
        }
        if ($domain && $autodomain) {
            $domain = substr(md5($_L['ROOTID'] + L_NAME + L_CLASS + L_ACTION), 8, 16) . "." . $domain;
        }
        $this->load_web_url($domain, $secure);
    }
}
