<?php
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
        self::load_tpl_config();
    }
    public function load_web_config()
    {
        global $_L;
        $_L['ROOTID'] = $_L['form']['rootid'] !== false ? $_L['form']['rootid'] : LCMS::X(403, "缺少必要参数，禁止访问");
    }
    public function load_web_url($domain = "", $secure = "")
    {
        global $_L;
        if ($_L['config']['web']['domain'] && $_L['config']['web']['domain_must'] && stristr(HTTP_HOST, $_L['config']['web']['domain']) === false) {
            LCMS::X(403, "请通过正确域名访问");
        }
        // 当前域名数据
        $domain                = $domain ? $domain : (HTTP_HOST ? HTTP_HOST : $_L['config']['web']['domain']);
        $_L['url']['secure']   = $secure ? $secure : ($_L['config']['web']['https'] == "1" ? "https://" : "http://");
        $_L['url']['site']     = $_L['url']['secure'] . $domain . "/";
        $_L['url']['now']      = $_L['url']['secure'] . $domain . HTTP_QUERY;
        $_L['url']['public']   = $_L['url']['site'] . 'public/';
        $_L['url']['static']   = $_L['url']['site'] . 'public/static/';
        $_L['url']['upload']   = $_L['url']['site'] . 'upload/';
        $_L['url']['cache']    = $_L['url']['site'] . 'cache/';
        $_L['url']['app']      = $_L['url']['site'] . 'app/';
        $_L['url']['own']      = $_L['url']['app'] . "index.php?" . ($_L['ROOTID'] > '0' ? 'rootid=' . $_L['ROOTID'] . '&' : '');
        $_L['url']['own_path'] = $_L['url']['app'] . L_TYPE . '/' . L_NAME . '/';
        $_L['url']['own_form'] = $_L['url']['own'] . "n=" . L_NAME . "&c=" . L_CLASS . "&a=";
        // 系统域名数据
        $_L['url']['sys']['domain'] = $_L['config']['web']['domain'];
        $_L['url']['sys']['secure'] = $_L['config']['web']['https'] == "1" ? "https://" : "http://";
        $_L['url']['sys']['site']   = $_L['url']['sys']['secure'] . $_L['config']['web']['domain'] . "/";
        $_L['url']['sys']['app']    = $_L['url']['sys']['site'] . "app/";
        $_L['url']['sys']['own']    = $_L['url']['sys']['app'] . "index.php?" . ($_L['ROOTID'] > '0' ? 'rootid=' . $_L['ROOTID'] . '&' : '');
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
    public static function load_tpl_config($tpl = "")
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
    public static function domain($domain = "", $secure = "", $autodomain = false)
    {
        global $_L;
        if (stripos($domain, "://") !== false) {
            $domain = parse_url($domain);
            $secure = $domain['scheme'] == "https" ? "https://" : "http://";
            $domain = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
        }
        if ($domain && $autodomain) {
            $domain = substr(md5($_L['ROOTID'] + L_NAME + L_CLASS + L_ACTION), 8, 16) . "." . $domain;
        }
        self::load_web_url($domain, $secure);
    }
}
