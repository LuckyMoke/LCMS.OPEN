<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:15:23
 * @LastEditTime: 2023-05-16 14:18:52
 * @Description: 用户登陆
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        parent::__construct();
        $LF   = $_L['form'];
        $CFG  = $_L['config']['admin'];
        $USER = $_L['LCMSADMIN'];
        //初始化ROOTID
        $RID = $_L['ROOTID'] = $LF['rootid'] != null ? $LF['rootid'] : (SESSION::get("LOGINROOTID") ?: 0);
    }
    /**
     * @description: 登陆首页
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        //如果域名不正确，跳转到正确域名
        if ($CFG['domain'] && $CFG['domain'] != HTTP_HOST && !$LF['fixed']) {
            okinfo(str_replace(HTTP_HOST, $CFG['domain'], $_L['url']['now']));
        }
        //如果已经登陆，跳转到后台首页
        if ($USER && $USER['id'] && $USER['name']) {
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        $UCFG = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => $RID == "0" ? true : $RID,
        ]) ?: [];
        SESSION::set("LOGINROOTID", $RID);
        //判断验证码模式
        if (empty($CFG['login_code']['type']) || !in_string(HTTP_HOST, $CFG['login_code']['domain'])) {
            //普通图形模式
            $CFG['login_code']['type'] = "default";
        }
        switch ($CFG['login_code']['type']) {
            case 'luosimao':
                load::plugin("Luosimao/captcha");
                $captcha = new CAPTCHA($CFG['login_code']['luosimao']);
                $yzcode  = $captcha->get();
                break;
            case 'vaptcha':
                load::plugin("Vaptcha/captcha");
                $captcha = new CAPTCHA($CFG['login_code']['vaptcha']);
                $yzcode  = $captcha->get();
                break;
            case 'geetest':
                load::plugin("Geetest/captcha");
                $captcha = new CAPTCHA($CFG['login_code']['geetest']);
                $yzcode  = $captcha->get();
                break;
            case 'recaptcha':
                load::plugin("Google/captcha");
                $captcha = new CAPTCHA($CFG['login_code']['recaptcha']);
                $yzcode  = $captcha->get();
                break;
        }
        if ($LF['action'] === "band") {
            $openid = SESSION::get("LOGINOPENID");
            if ($openid) {
                $page = [
                    "title" => "账号绑定",
                    "tab"   => [
                        ["title" => "账号绑定", "name" => "band"],
                    ],
                    "btn"   => "绑定",
                ];
            } else {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
        } else {
            $page = [
                "title" => "登陆 - {$CFG['title']}",
                "tab"   => [
                    ["title" => "账号登陆", "name" => "login"],
                ],
                "btn"   => "登陆",
            ];
            if ($UCFG['reg']['qrcode'] > 0) {
                $page['tab'] = array_merge($page['tab'], [[
                    "title" => "微信登陆",
                    "name"  => "qrcode",
                ]]);
            }
            if ($UCFG['reg']['qqlogin'] > 0) {
                $page['tab'] = array_merge($page['tab'], [[
                    "title" => "QQ登录",
                    "name"  => "qqlogin",
                ]]);
            }
        }
        $tplpath = is_dir(PATH_APP_NOW . "admin/tpl/custom") ? "custom" : "default";
        require LCMS::template("own/{$tplpath}/index");
    }
    /**
     * @description: 检测登陆状态
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        if ($LF['code']) {
            //图形验证码验证
            load::sys_class("captcha");
            CAPTCHA::check($LF['code']) || ajaxout(0, "验证码错误");
        }
        if (empty($CFG['login_code']['type']) || !in_string(HTTP_HOST, $CFG['login_code']['domain'])) {
            $LF['code'] || ajaxout(0, "验证码错误");
            $CFG['login_code']['type'] = "default";
        }
        switch ($CFG['login_code']['type']) {
            case 'luosimao':
                if ($LF['luotest_response']) {
                    load::plugin("Luosimao/captcha");
                    $YZ = new CAPTCHA($CFG['login_code']['luosimao']);
                    if (!$YZ->check($LF['luotest_response'])) {
                        ajaxout(0, "人机验证失败");
                    }
                } else {
                    ajaxout(0, "请进行人机验证");
                }
                break;
            case 'vaptcha':
                if ($LF['vaptcha_token']) {
                    load::plugin("Vaptcha/captcha");
                    $YZ = new CAPTCHA($CFG['login_code']['vaptcha']);
                    if (!$YZ->check($LF['vaptcha_server'], $LF['vaptcha_token'])) {
                        ajaxout(0, "人机验证失败");
                    }
                } else {
                    ajaxout(0, "请进行人机验证");
                }
                break;
            case 'geetest':
                if ($LF['GEETEST']) {
                    load::plugin("Geetest/captcha");
                    $YZ = new CAPTCHA($CFG['login_code']['geetest']);
                    if (!$YZ->check($LF['GEETEST'])) {
                        ajaxout(0, "人机验证失败");
                    }
                } else {
                    ajaxout(0, "请进行人机验证");
                }
                break;
            case 'recaptcha':
                if ($LF['code'] && $LF['recaptcha_token'] == SESSION::get("LCMSCAPTCHATOKEN")) {
                    SESSION::del("LCMSCAPTCHATOKEN");
                } elseif ($LF['recaptcha_token']) {
                    load::plugin("Google/captcha");
                    $YZ    = new CAPTCHA($CFG['login_code']['recaptcha']);
                    $score = $YZ->check($LF['recaptcha_token']);
                    if ($score > 0) {
                        if ($score < 0.5) {
                            SESSION::set("LCMSCAPTCHATOKEN", $LF['recaptcha_token']);
                            ajaxout(2);
                        }
                    } else {
                        ajaxout(0, "人机验证失败");
                    }
                } else {
                    ajaxout(0, "请进行人机验证");
                }
                break;
        }
        //获取用户数据
        $admin = sql_get(["admin",
            "pass = :pass AND (name = :name OR email = :name OR mobile = :name)",
            "id DESC",
            [
                ":name" => $LF['name'],
                ":pass" => md5($LF['pass']),
            ],
        ]);
        //如果无用户数据
        if (!$admin) {
            LCMS::log([
                "user" => $LF['name'],
                "type" => "login",
                "info" => "登陆失败-账号或密码错误",
            ]);
            ajaxout(0, "账号或密码错误");
        }
        //如果有用户数据
        if ($admin && $admin['status'] == 1) {
            if ($admin['lasttime'] > "0000-00-00 00:00:00" && $admin['lasttime'] < datenow()) {
                LCMS::log([
                    "user" => $admin['name'],
                    "type" => "login",
                    "info" => "登陆失败-此账户已到期",
                ]);
                ajaxout(0, "此账户已到期");
            } else {
                if ($LF['band'] > 0) {
                    $openid = SESSION::get("LOGINOPENID");
                    $openid || LCMS::X(403, "您未登录");
                    //绑定账号
                    $band = sql_get(["admin_band",
                        "openid = :openid AND aid = :aid",
                        "id DESC", [
                            ":openid" => $openid,
                            ":aid"    => $admin['id'],
                        ],
                    ]);
                    if (!$band) {
                        sql_insert(["admin_band", [
                            "openid" => $openid,
                            "aid"    => $admin['id'],
                        ]]);
                    }
                    LCMS::log([
                        "user" => $admin['name'],
                        "type" => "login",
                        "info" => "绑定账号-{$openid}",
                    ]);
                    ajaxout(1, "绑定成功", "goback");
                } else {
                    //登陆账号
                    $time = datenow();
                    sql_update(["admin", [
                        "logintime" => $time,
                        "ip"        => CLIENT_IP,
                    ], "id = '{$admin['id']}'"]);
                    $admin = array_merge($admin, [
                        "parameter" => sql2arr($admin['parameter']),
                        "logintime" => $time,
                    ]);
                    unset($admin['pass']);
                    SESSION::set("LCMSADMIN", $admin);
                    LCMS::log([
                        "user" => $admin['name'],
                        "type" => "login",
                        "info" => "登录成功",
                    ]);
                    ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin']);
                }
            }
        } else {
            ajaxout(0, "此账户已停用");
        }
    }
    /**
     * @description: 通过CID登陆
     * @param {*}
     * @return {*}
     */
    public function dologincid()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        if ($LF['cookie']) {
            $admin = ssl_decode($LF['cookie']);
            if ($admin) {
                $admin = json_decode($admin, true);
                SESSION::set("LCMSADMIN", $admin);
                LCMS::log([
                    "user" => $admin['name'],
                    "type" => "login",
                    "info" => "登陆成功-第三方登陆",
                ]);
                ajaxout(1, "success");
            }
        }
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    /**
     * @description: 登陆状态检测
     * @param {*}
     * @return {*}
     */
    public function doping()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        if ($_L['LCMSADMIN']) {
            ajaxout(1, "登陆成功", $LF['go'] ?: $_L['url']['admin']);
        } else {
            ajaxout(0, "failed");
        }
    }
    /**
     * @description: 获取二维码
     * @param {*}
     * @return {*}
     */
    public function dogetqrcode()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        $token = ssl_encode(json_encode([
            "cid"  => $_COOKIE['LCMSCID'],
            "time" => time() + 180,
        ]));
        if ($_SERVER['CONTENT_TYPE'] === "application/json" || (strcasecmp($_SERVER["HTTP_X_REQUESTED_WITH"], "xmlhttprequest") === 0)) {
            ajaxout(1, "success", "{$_L['url']['own_form']}index&c=qrcode&rootid={$RID}&token={$token}&name={$LF['name']}");
        } else {
            goheader("{$_L['url']['own_form']}index&c=qrcode&rootid={$RID}&token={$token}&name={$LF['name']}");
        }
    }
    /**
     * @description: 获取用户协议
     * @param {*}
     * @return {*}
     */
    public function doreadme()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        $config = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
        ]);
        switch ($LF['action']) {
            case 'user':
                $content = $config['readme']['user'];
                break;
            case 'privacy':
                $content = $config['readme']['privacy'];
                break;
        }
        ajaxout(1, "success", "", html_editor($content));
    }
    /**
     * @description: 退出登陆
     * @param {*}
     * @return {*}
     */
    public function dologinout()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        $_L['LCMSADMIN'] && LCMS::log([
            "user" => $_L['LCMSADMIN']['name'],
            "type" => "login",
            "info" => "退出登陆",
        ]);
        $RID = $_L['LCMSADMIN']['lcms'] ?: 0;
        SESSION::del("LCMSADMIN");
        okinfo("{$_L['url']['own']}rootid={$RID}&n=login&go=" . urlencode($LF['go']));
    }
}
