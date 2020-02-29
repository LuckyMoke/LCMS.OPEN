<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class reg extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
        $this->config = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
    }
    public function doindex()
    {
        global $_L;
        if (!$this->config['reg']) {
            LCMS::X(403, "未开启用户注册功能");
        }
        require LCMS::template("own/reg");
    }
    public function docheck()
    {
        global $_L;
        if (!$this->config['reg']) {
            LCMS::X(403, "未开启用户注册功能");
        }
        load::sys_class("captcha");
        switch ($_L['form']['action']) {
            case 'emailcode':
                if (captcha::check($_L['form']['code'])) {
                    if ($_L['form']['email']) {
                        $email = explode("@", $_L['form']['email']);
                        $black = "yopmail.com";
                        if (stristr($black, $email[1]) === false && $this->is_email($_L['form']['email'])) {
                            $emailcode = randstr(6);
                            session::set("LCMSREGEMAIL", $_L['form']['email']);
                            session::set("LCMSREGEMAILCODE", $emailcode);
                            load::sys_class("email");
                            email::$lcms = true;
                            $result      = email::send([
                                "to"      => $_L['form']['email'],
                                "toname"  => $_L['form']['user'],
                                "subject" => "邮箱验证码",
                                "body"    => "验证码：{$emailcode}，5分钟有效！",
                            ]);
                        } else {
                            $result['msg'] = "请输入正确的邮箱地址";
                        }
                    }
                    if ($result['code'] == "1") {
                        session::set("LCMSREGEMAILTIME", time() + 120);
                        ajaxout(1, "邮箱验证码发送成功，请进入邮箱查看");
                    } else {
                        ajaxout(0, $result['msg']);
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
            case 'name':
                $admin = sql_get(["admin", "name = '{$_L['form']['user']}'"]);
                if ($admin) {
                    ajaxout(0, "用户名已存在");
                }
                break;
            case 'email':
                $emailtime = session::get("LCMSREGEMAILTIME");
                if ($emailtime > time()) {
                    ajaxout(0, "请 " . ($emailtime - time()) . " 秒后再试");
                }
                if ($this->is_email($_L['form']['email'])) {
                    $admin = sql_get(["admin", "email = '{$_L['form']['email']}'"]);
                    if ($admin) {
                        ajaxout(0, "邮箱已存在");
                    }
                } else {
                    ajaxout(0, "请输入正确的邮箱地址");
                }
                break;
            default:
                $email     = session::get("LCMSREGEMAIL");
                $emailcode = session::get("LCMSREGEMAILCODE");
                $tuid      = session::get("LCMSADMINTUID");
                $admin     = $tuid ? sql_get(["admin", "id = '{$tuid}'"]) : "";
                if ($emailcode && $_L['form']['emailcode'] && $emailcode == strtoupper($_L['form']['emailcode'])) {
                    sql_insert(["admin", [
                        "tuid"    => $admin ? $tuid : 0,
                        "name"    => $_L['form']['user'],
                        "title"   => $_L['form']['user'],
                        "pass"    => md5($_L['form']['pass']),
                        "email"   => $email,
                        "addtime" => datenow(),
                        "type"    => $this->config['default_level'],
                    ]]);
                    if (sql_error()) {
                        ajaxout(0, "注册失败，请联系管理员");
                    } else {
                        ajaxout(1, "注册成功，请登陆");
                    }
                } else {
                    ajaxout(0, "邮箱验证码错误");
                }
                break;
        }
    }
    public function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
    }
}
