<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class("captcha");
load::sys_class("email");
class reg extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
        $rootid       = SESSION::get("LCMSLOGINROOTID");
        $this->rootid = $_L['form']['rootid'] != null ? $_L['form']['rootid'] : ($rootid ? $rootid : 0);
        SESSION::set("LCMSLOGINROOTID", $this->rootid);
        $this->config = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => $this->rootid,
        ]);
        if ($this->rootid != 0 && !$this->config) {
            LCMS::X("404", "未找到页面");
        }
        if ($this->config['reg']['on'] == null || $this->config['reg']['on'] == "0") {
            LCMS::X(403, "未开启用户注册功能");
        }
        $this->plugin = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "plugin",
            "lcms" => $this->rootid,
        ]);
    }
    public function doindex()
    {
        global $_L;
        if ($_L['LCMSADMIN'] && $_L['LCMSADMIN']['id'] && $_L['LCMSADMIN']['name']) {
            okinfo($_L['url']['admin']);
        }
        $config = $this->config;
        require LCMS::template("own/reg");
    }
    public function dojustuser()
    {
        global $_L;
        $config = $this->config;
        if (CAPTCHA::check($_L['form']['code'])) {
            $this->reg($config['reg']['on']);
        } else {
            ajaxout(0, "验证码错误");
        }
    }
    public function domobile()
    {
        global $_L;
        $config = $this->config;
        switch ($_L['form']['action']) {
            case 'code_ready':
                if (is_phone($_L['form']['mobile'])) {
                    $admininfo = sql_get(["admin", "name = :mobile OR email = :mobile OR mobile = :mobile", "id DESC", [
                        ":mobile" => $_L['form']['mobile'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "手机号已存在");
                    } else {
                        ajaxout(1, "success");
                    }
                } else {
                    ajaxout(0, "请输入正确的手机号");
                }
                break;
            case 'code_send':
                $time = SESSION::get("LCMSREGCODETIME");
                if ($time > time()) {
                    ajaxout(0, "请 " . ($time - time()) . " 秒后再试");
                }
                if (CAPTCHA::check($_L['form']['code'])) {
                    if ($config['reg']['sms_tplcode']) {
                        if (is_phone($_L['form']['mobile'])) {
                            $sendcode = randstr(6, "num");
                            SESSION::set("LCMSREGMOBILE", $_L['form']['mobile']);
                            SESSION::set("LCMSREGSENDCODE", $sendcode);
                            load::sys_class("sms");
                            $result = SMS::send([
                                "ID"    => $config['reg']['sms_tplcode'],
                                "Name"  => $config['reg']['sms_signname'],
                                "Phone" => $_L['form']['mobile'],
                                "Param" => [
                                    "code" => $sendcode,
                                ],
                            ], $this->plugin['sms']);
                            if ($result['code'] == 1) {
                                SESSION::set("LCMSREGCODETIME", time() + 120);
                                ajaxout(1, "验证码已发送");
                            } else {
                                ajaxout(0, $result['msg']);
                            }
                        } else {
                            ajaxout(0, "请输入正确的手机号");
                        }
                    } else {
                        ajaxout(0, "未开启短信功能");
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
            default:
                $this->reg($config['reg']['on']);
                break;
        }
    }
    public function doemail()
    {
        global $_L;
        $config = $this->config;
        switch ($_L['form']['action']) {
            case 'code_ready':
                if ($this->is_email($_L['form']['email'])) {
                    $admininfo = sql_get(["admin", "name = :email OR email = :email OR mobile = :email", "id DESC", [
                        ":email" => $_L['form']['email'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "邮箱已存在");
                    } else {
                        ajaxout(1, "success");
                    }
                } else {
                    ajaxout(0, "请输入正确的邮箱地址");
                }
                break;
            case 'code_send':
                $time = SESSION::get("LCMSREGCODETIME");
                if ($time > time()) {
                    ajaxout(0, "请 " . ($time - time()) . " 秒后再试");
                }
                if (CAPTCHA::check($_L['form']['code'])) {
                    $email = explode("@", $_L['form']['email']);
                    $black = "yopmail.com";
                    if (stristr($black, $email[1]) === false && $this->is_email($_L['form']['email'])) {
                        $sendcode = randstr(6, "num");
                        SESSION::set("LCMSREGEMAIL", $_L['form']['email']);
                        SESSION::set("LCMSREGSENDCODE", $sendcode);
                        $result = EMAIL::send([
                            "TO"    => $_L['form']['email'],
                            "Title" => "邮箱验证码",
                            "Body"  => "验证码为：{$sendcode}，5分钟有效！",
                        ], $this->plugin['email']);
                        if ($result['code'] == 1) {
                            SESSION::set("LCMSREGCODETIME", time() + 120);
                            ajaxout(1, "验证码已发送");
                        } else {
                            ajaxout(0, $result['msg']);
                        }
                    } else {
                        ajaxout(0, "请输入正确的邮箱地址");
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
            default:
                $this->reg($config['reg']['on']);
                break;
        }
    }
    private function reg($type = "mobile")
    {
        global $_L;
        $config = $this->config;
        if (mb_strlen($_L['form']['pass'], "UTF8") < 6) {
            ajaxout(0, "密码不能少于6位");
        }
        $sendcode  = SESSION::get("LCMSREGSENDCODE");
        $admininfo = [
            "name"    => $_L['form']['name'],
            "title"   => $_L['form']['title'] ? $_L['form']['title'] : $_L['form']['name'],
            "pass"    => md5($_L['form']['pass']),
            "status"  => $config['reg']['status'],
            "addtime" => datenow(),
            "type"    => $config['reg']['level'],
            "lcms"    => $config['reg']['lcms'],
        ];
        switch ($type) {
            case 'mobile':
                if ($sendcode && $sendcode == strtoupper($_L['form']['code'])) {
                    $mobile = SESSION::get("LCMSREGMOBILE");
                    if ($mobile) {
                        $admininfo['mobile'] = $mobile;
                    } else {
                        ajaxout(0, "缺少手机号");
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
            case 'email':
                if ($sendcode && $sendcode == strtoupper($_L['form']['code'])) {
                    $email = SESSION::get("LCMSREGEMAIL");
                    if ($email) {
                        $admininfo['email'] = $email;
                    } else {
                        ajaxout(0, "缺少邮箱地址");
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
        }
        sql_insert(["admin", $admininfo]);
        SESSION::del("LCMSREGSENDCODE");
        SESSION::del("LCMSREGMOBILE");
        SESSION::del("LCMSREGEMAIL");
        if (sql_error()) {
            ajaxout(0, "注册失败，请联系管理员");
        } else {
            ajaxout(1, "注册成功，请登陆");
        }
    }
    public function docheck()
    {
        global $_L;
        $config = $this->config;
        switch ($_L['form']['action']) {
            case 'name':
                if ($_L['form']['name']) {
                    if (strlen($_L['form']['name']) <= 6) {
                        ajaxout(0, "账号不能少于6位");
                    }
                    $admininfo = sql_get(["admin", "name = :name OR email = :name OR mobile = :name", "id DESC", [
                        ":name" => $_L['form']['name'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "账号已存在");
                    } else {
                        ajaxout(1, "success");
                    }
                } else {
                    ajaxout(0, "账号不能为空");
                }
                break;
        }
    }
    private function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
    }
}
