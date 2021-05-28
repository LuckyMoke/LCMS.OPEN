<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $CFGA;
        parent::__construct();
        $CFGA = $_L['config']['admin'];
    }
    public function doindex()
    {
        global $_L, $CFGA;
        if ($CFGA['domain'] && $CFGA['domain'] != HTTP_HOST && !$_L['form']['fixed']) {
            okinfo(str_replace(HTTP_HOST, $CFGA['domain'], $_L['url']['now']));
        }
        if ($_L['LCMSADMIN'] && $_L['LCMSADMIN']['id'] && $_L['LCMSADMIN']['name']) {
            okinfo($_L['url']['admin']);
        }
        $rootid = $_L['form']['rootid'] != null ? $_L['form']['rootid'] : 0;
        $config = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => $rootid,
        ]);
        if ($rootid != 0 && !$config) {
            LCMS::X("404", "未找到页面");
        }
        SESSION::set("LCMSLOGINROOTID", $rootid);
        if ($CFGA['login_code']['type'] && $CFGA['login_code']['type'] != "0" && stripos(HTTP_HOST, $CFGA['login_code']['domain']) !== false) {
            switch ($CFGA['login_code']['type']) {
                case 'luosimao':
                    load::plugin("Luosimao/captcha");
                    $captcha = new CAPTCHA($CFGA['login_code']['luosimao']);
                    $yzcode  = $captcha->get();
                    break;
            }
        }
        require LCMS::template("own/index");
    }
    public function docheck()
    {
        global $_L, $CFGA;
        if ($CFGA['login_code']['type'] && $CFGA['login_code']['type'] != "0" && stripos(HTTP_HOST, $CFGA['login_code']['domain']) !== false) {
            switch ($CFGA['login_code']['type']) {
                case 'luosimao':
                    if ($_L['form']['luotest_response']) {
                        load::plugin("Luosimao/captcha");
                        $captcha = new CAPTCHA($CFGA['login_code']['luosimao']);
                        $captcha->check($_L['form']['luotest_response']) ? "" : ajaxout(0, "人机验证失败");
                    } else {
                        ajaxout(0, "请进行人机验证");
                    }
                    break;
            }
        } else {
            if ($_L['form']['code']) {
                load::sys_class("captcha");
                captcha::check($_L['form']['code']) ? "" : ajaxout(0, "验证码错误");
            } else {
                ajaxout(0, "请输入验证码");
            };
        };
        $pass      = md5($_L['form']['pass']);
        $admininfo = sql_get(["admin", "(name = :name OR email = :name OR mobile = :name) AND pass = '{$pass}'", "id DESC", [
            ":name" => $_L['form']['name'],
        ]]);
        if ($admininfo) {
            if ($admininfo['status'] == '1') {
                if ($admininfo['lasttime'] > "0000-00-00 00:00:00" && $admininfo['lasttime'] < datenow()) {
                    ajaxout(0, "此账户已到期");
                } else {
                    $admininfo['parameter'] = sql2arr($admininfo['parameter']);
                    unset($admininfo['pass']);
                    $logintime              = datenow();
                    $admininfo['logintime'] = $logintime;
                    SESSION::set("LCMSADMIN", $admininfo);
                    sql_update(["admin", ["logintime" => $logintime, "ip" => CLIENT_IP], "id = '{$admininfo['id']}'"]);
                    ajaxout(1, "登录成功", $_L['form']['go'] ? $_L['form']['go'] : $_L['url']['admin']);
                }
            } else {
                ajaxout(0, "此账户已停用");
            }
        } else {
            ajaxout(0, "用户名或密码错误");
        }
    }
    public function dologinout()
    {
        global $_L;
        $rootid = SESSION::get("LCMSLOGINROOTID");
        SESSION::del("LCMSADMIN");
        okinfo("{$_L['url']['admin']}index.php?rootid={$rootid}&n=login&go=" . urlencode($_L['form']['go']));
    }
}
