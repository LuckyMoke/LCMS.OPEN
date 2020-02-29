<?php
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
        $this->check_level();
    }
    protected function load_admin_url()
    {
        global $_L;
        $_L['url']['secure']   = $_L['config']['admin']['https'] ? "https://" : ($_SERVER['HTTPS'] === 1 || $_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://");
        $_L['url']['site']     = $_L['url']['secure'] . HTTP_HOST . "/";
        $_L['url']['now']      = $_L['url']['secure'] . HTTP_HOST . HTTP_QUERY;
        $_L['url']['admin']    = $_L['url']['site'] . ($_L['config']['admin']['dir'] ? $_L['config']['admin']['dir'] : "admin") . '/';
        $_L['url']['public']   = $_L['url']['site'] . 'public/';
        $_L['url']['static']   = $_L['url']['site'] . 'public/static/';
        $_L['url']['upload']   = $_L['url']['site'] . 'upload/';
        $_L['url']['cache']    = $_L['url']['site'] . 'cache/';
        $_L['url']['app']      = $_L['url']['site'] . 'app/';
        $_L['url']['own']      = $_L['url']['app'] . "index.php?" . ($_L['ROOTID'] > '0' ? 'rootid=' . $_L['ROOTID'] . '&' : '');
        $_L['url']['own_path'] = $_L['url']['site'] . 'app/' . L_TYPE . '/' . L_NAME . '/';
        $_L['url']['own_form'] = $_L['url']['admin'] . "index.php?t=" . L_TYPE . "&n=" . L_NAME . "&c=" . L_CLASS . "&a=";
    }
    protected function check_login()
    {
        global $_L;
        $_L['LCMSADMIN'] = session::get("LCMSADMIN");
        if (stristr(HTTP_QUERY, "?n=login") === false && !$_L['LCMSADMIN']) {
            okinfo($_L['url']['admin'] . "index.php?n=login&go=" . urlencode($_L['url']['now']));
        } else {
            $admininfo = sql_get(["admin", "id = '{$_L['LCMSADMIN']['id']}'"]);
            if ($admininfo['logintime'] != $_L['LCMSADMIN']['logintime']) {
                session::del("LCMSADMIN");
                LCMS::X(403, "已在其它地方登陆账号，此设备自动退出", $_L['url']['admin'] . "index.php?n=login&go=" . urlencode($_L['url']['now']));
            }
            if ($admininfo['type'] != $_L['LCMSADMIN']['type']) {
                session::del("LCMSADMIN");
                LCMS::X(403, "系统权限已修改，请重新登陆", $_L['url']['admin'] . "index.php?n=login&go=" . urlencode($_L['url']['now']));
            }
            if ($_L['LCMSADMIN']['type'] != "lcms") {
                $level                    = sql_get(["admin_level", "id = '{$_L[LCMSADMIN][type]}'"]);
                $_L['LCMSADMIN']['level'] = sql2arr($level['parameter']);
            }
            $_L['ROOTID'] = isset($_L['LCMSADMIN']['lcms']) && $_L['LCMSADMIN']['lcms'] == "0" ? $_L['LCMSADMIN']['id'] : $_L['LCMSADMIN']['lcms'];
            $_L['ROOTID'] = $_L['config']['admin']['lcmsmode'] ? (LCMS::SUPER() ? "0" : $_L['ROOTID']) : "0";
        }
    }
    protected function load_web_url($domain = "", $secure = "")
    {
        global $_L;
        $domain                       = $domain ? $domain : ($_L['config']['web']['domain'] ? $_L['config']['web']['domain'] : HTTP_HOST);
        $_L['url']['web']['secure']   = $secure ? $secure : ($_L['config']['web']['https'] == "1" ? "https://" : "http://");
        $_L['url']['web']['site']     = $_L['url']['web']['secure'] . $domain . "/";
        $_L['url']['web']['public']   = $_L['url']['web']['site'] . 'public/';
        $_L['url']['web']['static']   = $_L['url']['web']['site'] . 'public/static/';
        $_L['url']['web']['upload']   = $_L['url']['web']['site'] . 'upload/';
        $_L['url']['web']['cache']    = $_L['url']['web']['site'] . 'cache/';
        $_L['url']['web']['app']      = $_L['url']['web']['site'] . 'app/';
        $_L['url']['web']['own']      = $_L['url']['web']['app'] . "index.php?" . ($_L['ROOTID'] > '0' ? 'rootid=' . $_L['ROOTID'] . '&' : '');
        $_L['url']['web']['own_path'] = $_L['url']['web']['site'] . 'app/' . L_TYPE . '/' . L_NAME . '/';
    }
    protected function load_app_info()
    {
        global $_L;
        $_L['APP'] = level::app();
    }
    protected function check_level()
    {
        global $_L;
        $fun = str_replace("do", "", L_ACTION);
        if ($_L['APP']['power'][L_CLASS][$fun]) {
            LCMS::X(403, "没有权限，禁止访问");
        }
    }
    protected function domain($domain = "", $secure = "", $autodomain = true)
    {
        global $_L;
        if ($domain && $autodomain) {
            $domain = substr(md5($_L['ROOTID'] + L_NAME + L_CLASS + L_ACTION), 8, 16) . "." . $domain;
        };
        self::load_web_url($domain, $secure);
    }
}
