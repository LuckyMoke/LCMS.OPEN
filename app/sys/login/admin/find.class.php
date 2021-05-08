<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class("captcha");
load::sys_class("email");
load::plugin("Aliyun/DySMS");
class find extends adminbase
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
        $config = $this->config;
        if ($config['reg']['findpass'] > "0") {
            require LCMS::template("own/find");
        } else {
            LCMS::X(403, "未开启找回密码功能");
        }
    }
    public function doreset()
    {
        global $_L;
        $config = $this->config;
        $name   = ssl_decode(SESSION::get("LCMSFINDSSLCODE"), "login");
        switch ($_L['form']['action']) {
            case 'save':
                if (mb_strlen($_L['form']['pass'], "UTF8") < 6) {
                    ajaxout(0, "密码不能少于6位");
                }
                if ($_L['form']['pass'] == $_L['form']['repass']) {
                    sql_update(["admin", [
                        "pass" => md5($_L['form']['pass']),
                    ], "email = '{$name}' OR mobile = '{$name}'"]);
                    SESSION::del("LCMSFINDSSLCODE");
                    ajaxout(1, "密码设置成功", "?n=login&rootid=" . $this->rootid);
                } else {
                    ajaxout(0, "两次密码不一样");
                }
                break;
            default:
                if ($name && ($this->is_email($name) || is_phone($name))) {
                    require LCMS::template("own/reset");
                } else {
                    okinfo("?n=login&c=find");
                }
                break;
        }
    }
    public function docheck()
    {
        global $_L;
        $config = $this->config;
        switch ($_L['form']['action']) {
            case 'name':
                $name = $_L['form']['name'];
                if ($name) {
                    if (strlen($name) <= 6) {
                        ajaxout(0, "账号不能少于6位");
                    }
                    if (!$this->is_email($name) && !is_phone($name)) {
                        $false = true;
                    }
                    if ($false) {
                        ajaxout(0, "请输入正确的邮箱或手机号");
                    } else {
                        ajaxout(1, "success");
                    }
                } else {
                    ajaxout(0, "账号不能为空");
                }
                break;
            case 'imgcode':
                $time = SESSION::get("LCMSFINDCODETIME");
                if ($time > time()) {
                    ajaxout(0, "请 " . ($time - time()) . " 秒后再试");
                }
                if (CAPTCHA::check($_L['form']['code'])) {
                    $name  = $_L['form']['name'];
                    $admin = sql_get(["admin", "email = '{$name}' OR mobile = '{$name}'"]);
                    if ($admin) {
                        if ($this->is_email($name)) {
                            $this->email($name, $admin['title']);
                        }
                        if (is_phone($name)) {
                            $this->mobile($name);
                        }
                    } else {
                        ajaxout(0, "此邮箱或手机号未注册");
                    }
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
            default:
                $name = SESSION::get("LCMSFINDNAME");
                $code = $_L['form']['code'];
                if ($name && $code && $code == SESSION::get("LCMSFINDSENDCODE")) {
                    SESSION::del("LCMSFINDNAME");
                    SESSION::del("LCMSFINDSENDCODE");
                    SESSION::set("LCMSFINDSSLCODE", ssl_encode($name, "login"));
                    ajaxout(1, "验证成功", "?n=login&c=find&a=reset&rootid=" . $this->rootid);
                } else {
                    ajaxout(0, "验证码错误");
                }
                break;
        }
    }
    public function email($email, $title)
    {
        global $_L;
        $config = $this->config;
        $code   = randstr(6, "num");
        SESSION::set("LCMSFINDNAME", $email);
        SESSION::set("LCMSFINDSENDCODE", $code);
        $result = EMAIL::send([
            "to"       => $email,
            "toname"   => $title,
            "subject"  => "邮箱验证码",
            "body"     => "验证码：{$code}，5分钟有效！",
            "fromname" => $this->plugin['email']['fromname'],
            "from"     => $this->plugin['email']['from'],
            "pass"     => $this->plugin['email']['pass'],
            "smtp"     => $this->plugin['email']['smtp'],
            "ssl"      => $this->plugin['email']['ssl'],
            "port"     => $this->plugin['email']['port'],
        ]);
        if ($result['code'] == 1) {
            SESSION::set("LCMSFINDCODETIME", time() + 120);
            ajaxout(1, "验证码已发送");
        } else {
            ajaxout(0, $result['msg']);
        }
    }
    public function mobile($mobile)
    {
        global $_L;
        $config = $this->config;
        if ($config['reg']['sms_tplcode']) {
            $code = randstr(6, "num");
            SESSION::set("LCMSFINDNAME", $mobile);
            SESSION::set("LCMSFINDSENDCODE", $code);
            $dysms = new DYSMS([
                "id"     => $this->plugin['alisms']['id'],
                "secret" => $this->plugin['alisms']['secret'],
                "sign"   => $this->plugin['alisms']['sign'],
            ]);
            $result = $dysms->send($config['reg']['sms_tplcode'], $mobile, [
                "code" => $code,
            ]);
            if ($result['code'] == 1) {
                SESSION::set("LCMSFINDCODETIME", time() + 120);
                ajaxout(1, "验证码已发送");
            } else {
                ajaxout(0, $result['msg']);
            }
        } else {
            ajaxout(0, "未开启短信功能");
        }
    }
    private function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
    }
}
