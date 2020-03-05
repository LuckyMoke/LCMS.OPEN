<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $config = LCMS::config(array(
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ));
        if ($_L['config']['admin']['login_code']['type'] && $_L['config']['admin']['login_code']['type'] != "0" && stripos(HTTP_HOST, $_L['config']['admin']['login_code']['domain']) !== false) {
            switch ($_L['config']['admin']['login_code']['type']) {
                case 'luosimao':
                    load::plugin("Luosimao/captcha");
                    $captcha = new CAPTCHA($_L['config']['admin']['login_code']['luosimao']);
                    $yzcode  = $captcha->get();
                    break;
            }
        }
        require LCMS::template("own/index");
    }
    public function docheck()
    {
        global $_L;
        if ($_L['config']['admin']['login_code']['type'] && $_L['config']['admin']['login_code']['type'] != "0" && stripos(HTTP_HOST, $_L['config']['admin']['login_code']['domain']) !== false) {
            switch ($_L['config']['admin']['login_code']['type']) {
                case 'luosimao':
                    if ($_L['form']['luotest_response']) {
                        load::plugin("Luosimao/captcha");
                        $captcha = new CAPTCHA($_L['config']['admin']['login_code']['luosimao']);
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
        $user      = $_L['form']['user'];
        $pass      = md5($_L['form']['pass']);
        $admininfo = sql_get(["admin", "name = '{$user}' AND pass = '{$pass}'"]);
        if ($admininfo) {
            if ($admininfo['status'] == '1') {
                if ($admininfo['lasttime'] > "0000-00-00 00:00:00" && $admininfo['lasttime'] < datenow()) {
                    ajaxout(0, "此账户已到期");
                } else {
                    $admininfo['parameter'] = sql2arr($admininfo['parameter']);
                    unset($admininfo['pass']);
                    $logintime              = datenow();
                    $admininfo['logintime'] = $logintime;
                    session::set("LCMSADMIN", $admininfo);
                    sql_update(["admin", ["logintime" => $logintime, "ip" => CLIENT_IP], "id = '{$admininfo[id]}'"]);
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
        session::del("LCMSADMIN");
        okinfo($_L['url']['admin'] . "index.php?n=login");
    }
}
