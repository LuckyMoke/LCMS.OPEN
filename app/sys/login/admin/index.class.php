<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:15:23
 * @LastEditTime: 2024-10-14 16:47:51
 * @Description: 用户登录
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::own_class('pub');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        parent::__construct();
        $LF   = $_L['form'];
        $CFG  = $_L['config']['admin'];
        $USER = $_L['LCMSADMIN'];
        $UCFG = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        if ($UCFG['login']['mode'] < 1) {
            $RID  = $_L['ROOTID']  = $LF['rootid'] != null ? $LF['rootid'] : (SESSION::get("LOGINROOTID") ?: 0);
            $UCFG = $RID > 0 ? LCMS::config([
                "name" => "user",
                "type" => "sys",
                "cate" => "admin",
                "lcms" => $RID,
            ]) : $UCFG;
        } else {
            $RID = $_L['ROOTID'] = 0;
        }
    }
    /**
     * @description: 登录首页
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        //基础目录权限检测
        if (!getdirpower("/cache")) {
            LCMS::X(403, "<code>/cache</code>目录无读写权限");
        }
        //如果域名不正确，禁止访问
        if ($CFG['domain'] && $CFG['domain'] != HTTP_HOST) {
            header("HTTP/1.1 404 Not Found");
            die;
        }
        //如果已经登录，跳转到后台首页
        if ($USER && $USER['id'] && $USER['name']) {
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        SESSION::set("LOGINROOTID", $RID);
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
                "title" => "登录 - {$CFG['title']}",
                "tab"   => [
                    ["title" => "账号登录", "name" => "login"],
                ],
                "btn"   => "登录",
            ];
            if ($UCFG['reg']['qrcode'] > 0) {
                $page['tab'] = array_merge($page['tab'], [[
                    "title" => "微信登录",
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
        //设置登录TOKEN
        $logincrt = [
            "token"   => randstr(32),
            "expires" => time() + 300,
        ];
        SESSION::set("LOGINTOKEN", $logincrt);
        setcookie("LCMSLOGINTOKEN", $logincrt['token'], $logincrt['expires']);
        require LCMS::template("own/{$tplpath}/index");
    }
    /**
     * @description: 检测登录状态
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        PUB::isLoginAttack();
        $LF['code'] || ajaxout(0, "验证码错误");
        if (!$LF['2fa']) {
            //图形验证码验证
            LOAD::sys_class("captcha");
            CAPTCHA::check($LF['code']) || ajaxout(0, "验证码错误");
        }
        //解密数据
        $token = SESSION::get("LOGINTOKEN");
        if ($token['expires'] > time()) {
            $token = md5($token['token']);
        } else {
            ajaxout(0, "登录超时/请重试", "reload");
        }
        $token || ajaxout(0, "账号或密码错误");
        $LF['name'] = openssl_decrypt($LF['name'], "AES-256-CBC", $token, 0, $token);
        $LF['name'] || ajaxout(0, "签名错误", "reload");
        $LF['name'] = substr($LF['name'], 8);
        $LF['pass'] = openssl_decrypt($LF['pass'], "AES-256-CBC", $token, 0, $token);
        $LF['pass'] || ajaxout(0, "签名错误", "reload");
        $LF['pass'] = substr($LF['pass'], 8);
        //获取用户数据
        $admin = sql_get(["admin",
            "name = :name OR email = :name OR mobile = :name",
            "id DESC", [
                ":name" => $LF['name'],
            ]]);
        if ($LF['2fa']) {
            //两步验证码验证
            LOAD::plugin("2FA/TOTP");
            (new TOTP())->verifyCode($admin['2fa'], $LF['code']) || ajaxout(0, "验证码错误");
        } elseif ($admin['2fa']) {
            ajaxout(2, "请输入两步验证码", "2fa");
        }
        $this->loginCheck($LF, $admin);
        SESSION::del("LOGINTOKEN");
        setcookie("LCMSLOGINTOKEN", "", time());
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
            $this->loginSuccess($admin);
            ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin']);
        }
    }
    /**
     * @description: 登录状态检测
     * @param {*}
     * @return {*}
     */
    public function doping()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        if ($_L['LCMSADMIN']) {
            $this->loginJwt($_L['LCMSADMIN']);
            ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin']);
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
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        $SID = SESSION::getid();
        switch ($LF['name']) {
            case 'qrcode':
                switch ($UCFG['reg']['qrcode']) {
                    case '2':
                        load::plugin("WeChat/OA");
                        $WX     = new OA();
                        $result = $WX->create_qrcode([
                            "expire_seconds" => 180,
                            "action_name"    => "QR_STR_SCENE",
                            "action_info"    => [
                                "scene" => [
                                    "scene_str" => "LOGIN|{$RID}|{$SID}",
                                ],
                            ],
                        ]);
                        if ($result['url']) {
                            $url = $result['url'];
                            SESSION::set("LOGINQRCODE", "{$_L['url']['own_form']}index&c=qrcode&rootsid={$SID}&rootid={$RID}&name={$LF['name']}");
                        } else {
                            ajaxout(0, "微信接口错误");
                        }
                        break;
                    case '1':
                        $url = "{$_L['url']['own_form']}index&c=qrcode&rootsid={$SID}&rootid={$RID}&name={$LF['name']}";
                        break;
                }
                break;
            case 'qqlogin':
                $url = "{$_L['url']['own_form']}index&c=qrcode&rootsid={$SID}&rootid={$RID}&name={$LF['name']}";
                if ($LF['click'] == "true") {
                    goheader($url);
                }
                break;
        }
        SESSION::set("LOGINQRCODETIME", time() + 180);
        ajaxout(1, "success", $url);
    }
    /**
     * @description: 获取用户协议
     * @param {*}
     * @return {*}
     */
    public function doreadme()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        switch ($LF['action']) {
            case 'user':
                $content = $UCFG['readme']['user'];
                break;
            case 'privacy':
                $content = $UCFG['readme']['privacy'];
                break;
        }
        ajaxout(1, "success", "", html_editor($content));
    }
    /**
     * @description: 登录byToken
     * @return {*}
     */
    public function dologinbytoken()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        $CFG['loginbytoken'] > 0 || LCMS::X(403, "未开启此功能");
        PUB::isLoginAttack();
        $LF['token'] || LCMS::X(403, "验证失败");
        $key  = md5($CFG['loginkey']);
        $form = openssl_decrypt($LF['token'], "AES-256-CBC", $key, 0, $key);
        $form || LCMS::X(403, "验证失败");
        $form = json_decode($form, true);
        $form || LCMS::X(403, "验证失败");
        $form['time'] < time() && LCMS::X(403, "验证失败");
        $islogin = SESSION::get("LCMSADMIN");
        if ($islogin && $islogin['name'] == $form['name']) {
            okinfo($_L['url']['admin']);
        } else {
            $admin = $this->loginCheck($form);
            $this->loginSuccess($admin);
            okinfo($_L['url']['admin']);
        }
    }
    /**
     * @description: 退出登录
     * @param {*}
     * @return {*}
     */
    public function dologinout()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        $RID = $_L['LCMSADMIN']['lcms'] ?: 0;
        if (LCMS::SUPER()) {
            $RID = 0;
        } elseif ($RID == 0) {
            $RID = $_L['LCMSADMIN']['id'];
        }
        if (!$UCFG || !in_array($UCFG['reg']['on'], ["mobile", "email"])) {
            $RID = 0;
        }
        $_L['LCMSADMIN'] && LCMS::log([
            "user" => $_L['LCMSADMIN']['name'],
            "type" => "login",
            "info" => "退出登录",
        ]);
        if ($UCFG['login']['jwt'] == 1) {
            sql_update([
                "table" => "admin",
                "data"  => [
                    "jwt" => null,
                ],
                "where" => "id = :id",
                "bind"  => [
                    ":id" => $_L['LCMSADMIN']['id'],
                ],
            ]);
            setcookie("LCMSJWT_" . urlsafe_base64_encode(HTTP_HOST), "", [
                "expires"  => time() - 3600,
                "domain"   => "." . roothost(HTTP_HOST),
                "path"     => "/",
                "secure"   => false,
                "httponly" => true,
                "samesite" => "Lax",
            ]);
        }
        SESSION::del("LCMSADMIN");
        okinfo("{$_L['url']['own']}rootid={$RID}&n=login&go=" . urlencode($LF['go']));
    }
    /**
     * @description: JWT有效性检测
     * @return {*}
     */
    public function docheckjwt()
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        if ($UCFG['login']['jwt'] == 1) {
            $LF['token'] || ajaxout(0, "failed");
            $token = explode(".", $LF['token'])[1];
            $token = $token ? base64_decode($token) : "";
            $token = $token ? json_decode($token, true) : [];
            if ($token['exp'] > time()) {
                $admin = sql_get([
                    "table" => "admin",
                    "where" => "id = :id",
                    "bind"  => [
                        ":id" => $token['id'],
                    ],
                ]);
                if (
                    $admin['status'] == 1 &&
                    (!$admin['lasttime'] || $admin['lasttime'] > datenow()) &&
                    jwt_decode($LF['token'], $admin['jwt'])
                ) {
                    unset($admin['pass'], $admin['jwt'], $admin['salt'], $admin['parameter']);
                    ajaxout(1, "success", "", $admin);
                }
            }
        }
        ajaxout(0, "failed");
    }
    /**
     * @description: 账户状态检测
     * @param array $form
     * @return bool
     */
    private function loginCheck($form, $admin = [])
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        $admin = $admin ?: sql_get(["admin",
            "name = :name OR email = :name OR mobile = :name",
            "id DESC", [
                ":name" => $form['name'],
            ]]);
        function loginFail($admin, $msg)
        {
            LCMS::log([
                "user" => $admin['name'],
                "type" => "login",
                "info" => "登录失败-{$msg}",
            ]);
            PUB::isLoginAttack("update");
            LCMS::X(403, $msg);
        }
        if ($admin) {
            if (md5("{$form['pass']}{$admin['salt']}") != $admin['pass']) {
                loginFail($admin, "账号或密码错误");
            }
            if ($admin['status'] == 1) {
                if ($admin['lasttime'] > "0000-00-00 00:00:00" && $admin['lasttime'] < datenow()) {
                    loginFail($admin, "此账号已到期，请联系客服");
                } else {
                    return $admin;
                }
            } else {
                loginFail($admin, "此账号已停用，请联系客服");
            }
        } else {
            loginFail($form, "账号或密码错误");
        }
    }
    /**
     * @description: 登录成功后操作
     * @param array $admin
     * @return {*}
     */
    private function loginSuccess($admin)
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        $data = [
            "logintime" => datenow(),
            "ip"        => CLIENT_IP,
        ];
        if ($UCFG['login']['jwt'] == 1) {
            $data['jwt'] = randstr(32);
        }
        sql_update([
            "table" => "admin",
            "data"  => $data,
            "where" => "id = :id",
            "bind"  => [
                ":id" => $admin['id'],
            ],
        ]);
        $admin = array_merge($admin, $data, [
            "parameter" => sql2arr($admin['parameter']),
        ]);
        unset($admin['pass']);
        SESSION::set("LCMSADMIN", $admin);
        $this->loginJwt($admin);
        LCMS::log([
            "user" => $admin['name'],
            "type" => "login",
            "info" => "登录成功",
        ]);
    }
    /**
     * @description: 生成单点登录JWT
     * @param array $admin
     * @return {*}
     */
    private function loginJwt($admin)
    {
        global $_L, $LF, $CFG, $UCFG, $USER, $RID;
        if ($UCFG['login']['jwt'] == 1) {
            $ltime = time() + 15552000;
            setcookie("LCMSJWT_" . urlsafe_base64_encode(HTTP_HOST), jwt_encode([
                "id"    => $admin['id'],
                "name"  => $admin['name'],
                "title" => $admin['title'],
                "exp"   => $ltime,
            ], $admin['jwt']), [
                "expires"  => $ltime,
                "domain"   => "." . roothost(HTTP_HOST),
                "path"     => "/",
                "secure"   => false,
                "httponly" => true,
                "samesite" => "Lax",
            ]);
        }
    }
}
