<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2025-04-11 16:27:01
 * @LastEditTime: 2025-12-19 12:32:11
 * @Description: 用户基础类
 * Copyright 2025 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class USERBASE
{
    private static $INIT = false;
    private static $UCFG = false;
    private static $MODE = 0;
    public static function init()
    {
        global $_L;
        if (!self::$INIT) {
            self::$INIT = true;
            self::$UCFG = LCMS::config([
                "name" => "user",
                "type" => "sys",
                "cate" => "admin",
                "lcms" => true,
            ]);
            //判断是总平台还是子平台
            self::$MODE = 1;
            if (self::$UCFG['login']['mode'] === "0") {
                self::$MODE = 0;
            }
            if (L_MODULE == 'admin') {
                if (isset($_L['ROOTID'])) {
                    $_L['ROOTID'] = intval($_L['ROOTID']);
                }
                //如果ROOTID不存在
                if (!isset($_L['ROOTID'])) {
                    if (isset($_L['form']['rootid'])) {
                        $formrid = intval($_L['form']['rootid']);
                    }
                    if (isset($_L['cookie']['LCMSLOGINROOTID'])) {
                        $cookierid = intval($_L['cookie']['LCMSLOGINROOTID']);
                    }
                    if (isset($formrid)) {
                        //如果form里rootid
                        $_L['ROOTID'] = $formrid;
                    } elseif (isset($cookierid)) {
                        //如果cookie里rootid
                        $_L['ROOTID'] = $cookierid;
                    }
                    $_L['ROOTID'] = $_L['ROOTID'] ?: 0;
                }
                if (self::$MODE < 1) {
                    //如果开启了子平台
                    $cookierid = $_L['ROOTID'];
                } else {
                    //如果强制总平台
                    $cookierid = 0;
                }
                setcookie("LCMSLOGINROOTID", $cookierid, [
                    "expires"  => time() + 31536000,
                    "path"     => "/",
                    "secure"   => false,
                    "httponly" => true,
                ]);
            }
            if (self::$MODE < 1) {
                //如果开启了子平台
                if ($_L['ROOTID'] > 0) {
                    self::$UCFG = LCMS::config([
                        "name" => "user",
                        "type" => "sys",
                        "cate" => "admin",
                        "lcms" => $_L['ROOTID'],
                    ]);
                }
            } else {
                //如果强制总平台
                if (
                    $_L['ROOTID'] > 0 &&
                    in_string($_L['url']['now'], "rootid={$_L['ROOTID']}")
                ) {
                    header("HTTP/1.1 403 Forbidden");
                    exit;
                }
            }
        }
    }
    /**
     * @description: 获取配置
     * @return array
     */
    public static function UCFG()
    {
        global $_L;
        if (
            $_L['ROOTID'] > 0 &&
            !self::$UCFG &&
            (L_NAME == "login" && L_ACTION != "dologinout")
        ) {
            LCMS::X(403, "未获取到用户配置");
        }
        return self::$UCFG ?: [];
    }
    /**
     * @description: 登录签名获取/验证
     * @param string $type
     * @param string $encode
     * @return string|bool
     */
    public static function token($type = "get", $encode = "")
    {
        global $_L;
        switch ($type) {
            case 'check':
                $cert = SESSION::get("LCMSLOGINTOKEN");
                $cert || ajaxout(0, "签名错误", "reload");
                if ($cert['expires'] > time()) {
                    $token = md5($cert['token']);
                } else {
                    ajaxout(0, "登录超时", "reload");
                }
                return $token;
                break;
            case 'decode':
                $cert   = SESSION::get("LCMSLOGINTOKEN");
                $token  = md5($cert['token']);
                $decode = openssl_decrypt($encode, "AES-256-CBC", $token, 0, $token);
                $decode || ajaxout(0, "签名错误", "reload");
                return $decode;
                break;
            case 'clear':
                SESSION::del("LCMSLOGINTOKEN");
                setcookie("LCMSLOGINTOKEN", "", time());
                break;
            default:
                $cert = [
                    "token"   => randstr(32),
                    "expires" => time() + 300,
                ];
                SESSION::set("LCMSLOGINTOKEN", $cert);
                setcookie("LCMSLOGINTOKEN", $cert['token'], $cert['expires']);
                return $cert['token'];
                break;
        }
    }
    /**
     * @description: 用户登录检测
     * @param string $opts [by、name、pass、code、2fa、jwt、band、go、openid、webuser]
     * @return {*}
     */
    public static function login($opts = [])
    {
        global $_L;
        $opts = is_array($opts) ? $opts : [];
        $opts = array_merge([
            "by" => "pass",
        ], $opts);
        //是否前端用户
        if ($opts['webuser']) {
            $_L['_tmp_webuser'] = true;
        }
        switch ($opts['by']) {
            case 'wechat':
            case 'qrcode':
            case 'qq':
            case 'qqlogin':
                //by、name、openid
                if ($opts['name']) {
                    //获取用户数据
                    $user = self::getUser($opts['name']);
                    //验证账号是否绑定
                    self::checkBand($opts, $user);
                } else {
                    //获取绑定用户
                    $openid = $opts['openid'] ?: self::getOpenid("session");
                    $user   = self::getBand($openid, 1);
                }
                if ($user) {
                    //验证用户是否可用
                    self::checkUser($opts, $user);
                    //登录成功
                    self::loginSuccess($user, "第三方登录");
                }
                return $user ?: [];
                break;
            case 'sms':
            case 'mobile':
                //验证码检测
                $to = self::checkSendCode($opts['vcode']);
                //获取用户信息
                $user = self::getUser($to, "mobile");
                //登录成功
                if ($user) {
                    self::loginSuccess($user, "手机号登录");
                }
                return $user ?: [];
                break;
            case 'email':
                //验证码检测
                $to = self::checkSendCode($opts['vcode']);
                //获取用户信息
                $user = self::getUser($to, "email");
                //登录成功
                if ($user) {
                    self::loginSuccess($user, "邮箱登录");
                }
                return $user ?: [];
                break;
            case 'jwt':
                if (self::UCFG()['login']['jwt'] == 1) {
                    $opts['jwt'] || ajaxout(0, "JWT无效");
                    $jwt = explode(".", $opts['jwt'])[1];
                    $jwt = $jwt ? base64_decode($jwt) : "";
                    $jwt = $jwt ? json_decode($jwt, true) : [];
                    if ($jwt['exp'] > time()) {
                        $user = sql_get([
                            "table" => "admin",
                            "where" => "id = :id" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
                            "bind"  => [
                                ":id"   => $jwt['id'],
                                ":lcms" => $_L['ROOTID'],
                            ]]);
                        $user || ajaxout(0, "JWT无效");
                        if (
                            $user['status'] == 1 &&
                            (!$user['lasttime'] || $user['lasttime'] > datenow()) &&
                            jwt_decode($opts['jwt'], $user['jwt'])
                        ) {
                            unset($user['pass'], $user['jwt'], $user['salt'], $user['parameter']);
                            return $user;
                        }
                        ajaxout(0, "JWT登录失败");
                    }
                }
                ajaxout(0, "JWT登录失败");
                break;
            default:
                self::checkAttack("login");
                $is2fa = $opts['2fa'] ?: false;
                //如果不是2fa登录，则验证图形验证码
                $is2fa || self::checkCode($opts['code']);
                //获取用户数据
                $user = self::getUser($opts['name']);
                $user || ajaxout(0, "用户不存在");
                //判断用户类型
                if (
                    array_key_exists("cate", $opts) &&
                    $opts['cate'] != $user['cate']
                ) {
                    ajaxout(0, "未找到用户信息");
                }
                //两步验证检查
                if ($is2fa) {
                    self::check2fa($user['2fa'], $opts['2fa']);
                } elseif ($user['2fa']) {
                    ajaxout(2, "请输入两步验证码", "2fa");
                }
                //验证用户是否可用
                self::checkUser($opts, $user);
                //判断是登录还是绑定
                if ($opts['band']) {
                    self::userBand($user);
                } else {
                    self::loginSuccess($user, "密码登录");
                }
                return $user;
                break;
        }
    }
    /**
     * @description: 用户注册
     * @param array $opts [by、name、pass、title、cate、vcode]
     * @return {*}
     */
    public static function register($opts = [])
    {
        global $_L;
        switch ($opts['by']) {
            case 'sms':
            case 'mobile':
            case 'email':
            case 'vcode':
                if ($opts['vcode'] !== false) {
                    //判断验证码是否正确
                    $to = self::checkSendCode($opts['vcode']);
                    if (is_email($to)) {
                        $opts['email'] = $to;
                    } else {
                        $opts['mobile'] = $to;
                    }
                }
                break;
            case 'openid':
                $openid = $opts['openid'] ?: self::getOpenid("session");
                $openid || ajaxout(1, "注册失败");
                break;
        }
        $user = self::update($opts);
        $user = self::getUser($user['name']);
        $user || ajaxout(1, "注册失败");
        switch ($opts['by']) {
            case 'openid':
                //用户绑定
                self::userBand($user, $openid);
                break;
        }
        //超管下级用户注册，默认打开第一个应用
        if ($_L['ROOTID'] == 0 && self::UCFG()['reg']['defaultapp'] > 0) {
            LCMS::config([
                "do"   => "save",
                "name" => "menu",
                "type" => "sys",
                "cate" => "admin",
                "lcms" => $user['id'],
                "form" => [
                    "default" => [
                        "on"   => 1,
                        "name" => "",
                    ],
                ],
            ]);
        }
        return $user;
    }
    /**
     * @description: 添加/更新用户信息
     * @param array $opts
     * @param array $user
     * @return array
     */
    public static function update($opts, $user = [])
    {
        global $_L;
        //检测用户信息格式
        $data = self::checkFormat($opts, $user);
        LCMS::form([
            "table" => "admin",
            "form"  => $data,
            "unset" => array_key_exists("level", $data) ? "level" : "",
        ]);
        if (sql_error()) {
            ajaxout(0, "保存失败", "", sql_error());
        } else {
            LCMS::log([
                "user" => $_L['LCMSADMIN'] ? $_L['LCMSADMIN']['name'] : $data['name'],
                "type" => "system",
                "info" => "用户管理：" . ($data['id'] ? "修改" : "添加") . "/{$data['name']}",
            ]);
            $user = array_merge($user, $data);
            return $user;
        }
    }
    /**
     * @description: 找回密码
     * @param array $opts [to、pass]
     * @return {*}
     */
    public static function reset($opts = [])
    {
        global $_L;
        if (
            !is_email($opts['to']) &&
            !is_phone($opts['to'])
        ) {
            ajaxout(0, "手机号或邮箱不正确");
        }
        $user = self::getUser($opts['to'], "mobile|email");
        $user || ajaxout(0, "用户不存在");
        //更新密码
        self::update($opts, $user);
        LCMS::log([
            "user" => $user['name'],
            "type" => "login",
            "info" => "找回密码",
        ]);
        return true;
    }
    /**
     * @description: 退出登录
     * @param string $go
     * @return string
     */
    public static function loginout($go = "")
    {
        global $_L;
        $user = SESSION::get("LCMSADMIN");
        if (isset($_L['cookie']['LCMSLOGINROOTID'])) {
            $_L['ROOTID'] = intval($_L['cookie']['LCMSLOGINROOTID']);
        }
        $_L['ROOTID'] = $_L['ROOTID'] ?: 0;
        $user && LCMS::log([
            "user" => $user['name'],
            "type" => "login",
            "info" => "退出登录",
        ]);
        //清理JWT
        self::clearJWT($user);
        if (!$go) {
            $go = "{$_L['url']['own']}rootid={$_L['ROOTID']}&n=login&go=" . urlencode($_L['form']['go']);
        }
        SESSION::del("LCMSADMIN");
        return $go;
    }
    /**
     * @description: 第三方登录绑定
     * @param array $user
     * @param string $openid
     * @return {*}
     */
    public static function userBand($user, $openid = "")
    {
        global $_L;
        $openid = $openid ?: self::getOpenid("session");
        $openid || ajaxout(0, "绑定失败");
        if (!$user['id']) {
            $user = self::getUser($user['name']);
        }
        $band = sql_get([
            "table" => "admin_band",
            "where" => "openid = :openid AND aid = :aid" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
            "order" => "id DESC",
            "bind"  => [
                ":openid" => $openid,
                ":aid"    => $user['id'],
                ":lcms"   => $_L['ROOTID'],
            ]]);
        if (!$band) {
            sql_insert([
                "table" => "admin_band",
                "data"  => [
                    "openid" => $openid,
                    "aid"    => $user['id'],
                    "lcms"   => $_L['ROOTID'],
                ]]);
        }
        LCMS::log([
            "user" => $user['name'],
            "type" => "login",
            "info" => "绑定账号：{$openid}",
        ]);
        return $user;
    }
    /**
     * @description: 检查用户是否绑定第三方
     * @param array $opts [by、openid]
     * @param array $user [id]
     * @return {*}
     */
    public static function checkBand($opts, $user)
    {
        global $_L;
        if ($opts['by'] == "qq" || $opts['by'] == "qqlogin") {
            $opts['openid'] = "QQ{$opts['openid']}";
        }
        $band = sql_get([
            "table" => "admin_band",
            "where" => "openid = :openid AND aid = :aid" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
            "bind"  => [
                ":openid" => $opts['openid'],
                ":aid"    => $user['id'],
                ":lcms"   => $_L['ROOTID'],
            ]]);
        $band || ajaxout(0, "未绑定此账号");
    }
    /**
     * @description: 获取绑定用户列表
     * @param string $openid
     * @param int $limit
     * @return array
     */
    public static function getBand($openid, $limit = 0)
    {
        global $_L;
        if ($limit > 0) {
            $bands = sql_get([
                "table" => "admin_band",
                "where" => "openid = :openid" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
                "order" => "id DESC",
                "bind"  => [
                    ":openid" => $openid,
                    ":lcms"   => $_L['ROOTID'],
                ]]);
            $aids = $bands ? $bands['aid'] : "";
        } else {
            $bands = sql_get([
                "table"  => "admin_band",
                "where"  => "openid = :openid" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
                "order"  => "id ASC",
                "bind"   => [
                    ":openid" => $openid,
                    ":lcms"   => $_L['ROOTID'],
                ],
                "fields" => "GROUP_CONCAT(aid) aids"]);
            $aids = $bands ? $bands['aids'] : "";
        }
        $users = $aids ? sql_getall([
            "table" => "admin",
            "where" => "id IN ({$aids}) AND status = 1 AND (lasttime IS NULL OR lasttime > :lasttime)",
            "bind" => [
                ":lasttime" => datenow(),
            ],
        ]) : [];
        return $users;
    }
    /**
     * @description: 解除用户第三方绑定
     * @param array $opts [name、openid]
     * @return {*}
     */
    public static function unBand($opts = [])
    {
        global $_L;
        $user = self::getUser($opts['name']);
        $user || ajaxout(0, "解绑失败");
        sql_delete([
            "table" => "admin_band",
            "where" => "openid = :openid AND aid = :aid" . (self::$MODE > 0 ? "" : " AND lcms = :lcms"),
            "bind"  => [
                ":openid" => $opts['openid'],
                ":aid"    => $user['id'],
                ":lcms"   => $_L['ROOTID'],
            ],
        ]);
        return true;
    }
    /**
     * @description: 发送验证码
     * @param array $opts [by、to、code]
     * @return string
     */
    public static function sendCode($opts = [])
    {
        global $_L;
        //判断是否已发送过验证码
        $time = SESSION::get("LCMSSENDTIME");
        if ($time > time()) {
            $time = $time - time();
            ajaxout(0, "请 {$time} 秒后再试", "", $time);
        }
        self::checkAttack($opts['by'], "check", $opts['to']);
        //判断图形验证码是否正确
        if ($opts['code'] !== false) {
            self::checkCode($opts['code']);
        }
        $code = randstr(6, "num");
        $UCFG = self::UCFG()['reg'];
        switch ($opts['by']) {
            case 'sms':
            case 'mobile':
                $black = $UCFG['sms_black'];
                if (
                    !is_phone($opts['to']) ||
                    ($black && preg_match("/^({$black})\d+$/", $opts['to']))
                ) {
                    ajaxout(0, "此号码无法接收验证码，请联系客服处理");
                }
                LOAD::sys_class("sms");
                $result = SMS::send([
                    "ID"    => $UCFG['sms_tplcode'],
                    "Name"  => $UCFG['sms_signname'],
                    "Phone" => $opts['to'],
                    "Param" => [
                        "code" => $code,
                    ],
                ]);
                break;
            case 'email':
                if (
                    !is_email($opts['to']) ||
                    preg_match("/^[a-zA-Z0-9_-]+@(yopmail.com)$/", $opts['to'])
                ) {
                    ajaxout(0, "此邮箱无法接收验证码，请联系客服处理");
                }
                LOAD::sys_class("email");
                $result = EMAIL::send([
                    "TO"    => $opts['to'],
                    "Title" => "邮箱验证码",
                    "Body"  => "验证码为：{$code}，5分钟有效！",
                ]);
                break;
        }
        if ($result['code'] == 1) {
            self::checkAttack($opts['by'], "update", $opts['to']);
            SESSION::set("LCMSSENDTO", $opts['to']);
            SESSION::set("LCMSSENDCODE", $code);
            SESSION::set("LCMSSENDTIME", time() + 120);
            return $code;
        } else {
            ajaxout(0, $result['msg']);
        }
    }
    /**
     * @description: 判断发送的验证码是否有效
     * @param string $vcode
     * @return string
     */
    public static function checkSendCode($vcode)
    {
        global $_L;
        //判断验证码是否过期
        // $time = SESSION::get("LCMSSENDTIME");
        // if ($time <= time()) {
        //     ajaxout(0, "验证码已过期");
        // }
        //判断验证码是否正确
        $code = SESSION::get("LCMSSENDCODE");
        if (!$code || $code != strtoupper($vcode)) {
            ajaxout(0, "验证码错误");
        }
        $to = SESSION::get("LCMSSENDTO");
        $to || ajaxout(0, "验证码错误");
        //删除缓存数据
        SESSION::del("LCMSSENDTO");
        SESSION::del("LCMSSENDCODE");
        SESSION::del("LCMSSENDTIME");
        return $to;
    }
    /**
     * @description: 获取第三方openid
     * @param string $type [qrcode|qqlogin|session]
     * @return string
     */
    public static function getOpenid($type = "qrcode")
    {
        global $_L;
        $type = $type ?: "qrcode";
        if ($type == "session") {
            return SESSION::get("LCMSLOGINOPENID");
        }
        if (SESSION::get("LCMSADMIN")) {
            LCMS::Y(200, "用户已登录");
        }
        if (SESSION::get("LCMSLOGINQRTIME") < time()) {
            LCMS::X(403, "请重新扫码");
        }
        $UCFG = self::UCFG()['reg'];
        switch ($type) {
            case 'wechat':
            case 'qrcode':
                if ($UCFG['qrcode'] < 1) {
                    header("HTTP/1.1 403 Forbidden");
                    exit;
                }
                LOAD::plugin("WeChat/OA");
                $openid = (new OA())->openid()['openid'];
                break;
            case 'qq':
            case 'qqlogin':
                if ($UCFG['qqlogin'] < 1) {
                    header("HTTP/1.1 403 Forbidden");
                    exit;
                }
                LOAD::plugin("Tencent/QQConnect");
                $QQ = new QQConnect([
                    "appid"   => $UCFG['qqlogin_appid'],
                    "domain"  => $UCFG['qqlogin_domain'],
                    "display" => "pc",
                ]);
                $openid = $QQ->openid();
                $openid || LCMS::X(403, "登录失败");
                $openid = "QQ{$openid}";
                break;
        }
        $openid || LCMS::X(403, "登录失败");
        SESSION::set("LCMSLOGINOPENID", $openid);
        return $openid;
    }
    /**
     * @description: 创建第三方登录二维码
     * @param string $by
     * @return string
     */
    public static function createQrcode($by = "wechat")
    {
        global $_L;
        $sid  = SESSION::getid();
        $time = time();
        switch ($by) {
            case "wechat":
            case "qrcode":
                switch (self::UCFG()['reg']['qrcode']) {
                    case '1':
                        $url = "{$_L['url']['own_form']}index&c=qrcode&rootsid={$sid}&rootid={$_L['ROOTID']}&action=qrcode&_={$time}";
                        break;
                    case '2':
                        LOAD::plugin("WeChat/OA");
                        $WX     = new OA();
                        $result = $WX->create_qrcode([
                            "expire_seconds" => 180,
                            "action_name"    => "QR_STR_SCENE",
                            "action_info"    => [
                                "scene" => [
                                    "scene_str" => "LOGIN|{$_L['ROOTID']}|{$sid}",
                                ],
                            ],
                        ]);
                        if ($result['url']) {
                            $url = $result['url'];
                            SESSION::set("LCMSLOGINQRGO", "{$_L['url']['own_form']}index&c=qrcode&rootsid={$sid}&rootid={$_L['ROOTID']}&action=qrcode");
                        } else {
                            ajaxout(0, "获取微信登录二维码失败");
                        }
                        break;
                }
                break;
            case "qq":
            case "qqlogin":
                $url = "{$_L['url']['own_form']}index&c=qrcode&rootsid={$sid}&rootid={$_L['ROOTID']}&action=qqlogin&_={$time}";
                break;
            default:
                ajaxout(0, "暂不支持该登录方式");
                break;
        }
        SESSION::set("LCMSLOGINQRTIME", time() + 180);
        return $url;
    }
    /**
     * @description: 登录成功后操作
     * @param array $user
     * @return {*}
     */
    public static function loginSuccess($user, $msg = "")
    {
        global $_L;
        if ($user['status'] <= 0) {
            return;
        }
        $user = array_merge($user, [
            "logintime" => datenow(),
            "ip"        => CLIENT_IP,
            "parameter" => sql2arr($user['parameter']),
        ]);
        if (self::UCFG()['login']['jwt'] == 1) {
            $user['jwt'] = randstr(32);
        }
        sql_update([
            "table" => "admin",
            "data"  => [
                "logintime" => $user['logintime'],
                "jwt"       => $user['jwt'] ?: "",
                "ip"        => $user['ip'],
            ],
            "where" => "id = :id",
            "bind"  => [
                ":id" => $user['id'],
            ],
        ]);
        unset($user['pass']);
        if ($_L['_tmp_webuser']) {
            $user['webuser'] = true;
        }
        SESSION::set("LCMSADMIN", $user);
        self::createJWT($user);
        setcookie("LCMSUSERCATE", $user['cate'], time() + 15552000, "/", "", 0, true);
        LCMS::log([
            "user" => $user['name'],
            "type" => "login",
            "info" => "登录成功" . ($msg ? "：{$msg}" : ""),
        ]);
    }
    /**
     * @description: 登录失败后操作
     * @param array $user
     * @param string $msg
     * @return {*}
     */
    public static function loginFail($user, $msg)
    {
        global $_L;
        LCMS::log([
            "user" => $user['name'],
            "type" => "login",
            "info" => "登录失败" . ($msg ? "：{$msg}" : ""),
        ]);
        self::checkAttack("login", "update");
        ajaxout(0, $msg);
    }
    /**
     * @description: 生成单点登录JWT
     * @param array $user
     * @return {*}
     */
    public static function createJWT($user)
    {
        global $_L;
        if (self::UCFG()['login']['jwt'] == 1) {
            $ltime = time() + 15552000;
            setcookie("LCMSJWT_" . urlsafe_base64_encode(HTTP_HOST), jwt_encode([
                "id"    => $user['id'],
                "name"  => $user['name'],
                "title" => $user['title'],
                "exp"   => $ltime,
            ], $user['jwt']), [
                "expires"  => $ltime,
                "domain"   => "." . roothost(HTTP_HOST),
                "path"     => "/",
                "secure"   => false,
                "httponly" => true,
                "samesite" => "Lax",
            ]);
        }
    }
    /**
     * @description: 清理JWT
     * @param array $user
     * @return {*}
     */
    public static function clearJWT($user)
    {
        global $_L;
        if (self::UCFG()['login']['jwt'] == 1) {
            sql_update([
                "table" => "admin",
                "data"  => [
                    "jwt" => null,
                ],
                "where" => "id = :id",
                "bind"  => [
                    ":id" => $user['id'],
                ],
            ]);
        }
        setcookie("LCMSJWT_" . urlsafe_base64_encode(HTTP_HOST), "", [
            "expires"  => time() - 3600,
            "domain"   => "." . roothost(HTTP_HOST),
            "path"     => "/",
            "secure"   => false,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
    }
    /**
     * @description: 账户状态检测
     * @param array $opts
     * @param array $user
     * @return bool
     */
    public static function checkUser($opts = [], $user = [])
    {
        global $_L;
        $user = $user ?: ($opts['name'] ? self::getUser($opts['name']) : []);
        if ($user) {
            if ($opts['pass']) {
                $pass = md5("{$opts['pass']}{$user['salt']}");
                if ($pass != $user['pass']) {
                    self::loginFail($user, "账号或密码错误");
                }
            }
            if ($user['status'] == 1) {
                if ($user['lasttime'] > "0000-00-00 00:00:00" && $user['lasttime'] < datenow()) {
                    self::loginFail($user, "此账号已到期，请联系客服");
                } else {
                    return $user;
                }
            } else {
                self::loginFail($user, "此账号已停用，请联系客服");
            }
        } else {
            self::loginFail($opts, "用户不存在");
        }
    }
    /**
     * @description: 检测账号是否存在
     * @param string $type
     * @param string $data
     * @param int $jump
     * @return {*}
     */
    public static function isHave($by, $data = "", $jump = false)
    {
        global $_L;
        switch ($by) {
            case 'name':
                $title = "账号";
                if (strlen($data) < 6) {
                    ajaxout(0, "账号不能少于6位");
                }
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $data)) {
                    ajaxout(0, "账号只能是字母、数字、下划线");
                }
                break;
            case 'mobile':
                $title = "手机号";
                is_phone($data) || ajaxout(0, "手机号错误");
                break;
            case 'email':
                $title = "邮箱";
                is_email($data) || ajaxout(0, "邮箱地址错误");
                break;
            default:
                ajaxout(0, "账号类型错误");
                break;
        }
        $user = self::getUser($data, $by);
        if ($jump) {
            if ($user['id'] == $jump) {
                $user = [];
            } elseif ($user[$by] != $data) {
                $user = [];
            }
        }
        $user && ajaxout(0, "{$title}已存在");
    }
    /**
     * @description: 获取用户信息
     * @param string $name
     * @return array
     */
    public static function getUser($data = "", $limit = "")
    {
        global $_L;
        $where  = [];
        $limits = $limit ? explode("|", $limit) : [
            "name", "mobile", "email",
        ];
        foreach ($limits as $v) {
            switch ($v) {
                case 'id':
                    $where[] = "id = :data";
                    break;
                case 'name':
                    $where[] = "name = :data";
                    break;
                case 'mobile':
                    $where[] = "mobile = :data";
                    break;
                case 'email':
                    $where[] = "email = :data";
                    break;
            }
        }
        $where = implode(" OR ", $where);
        if (!in_array("id", $limits) && self::$MODE < 1) {
            //开启子平台
            $where = "($where)";
            if ($_L['ROOTID'] > 0) {
                $where .= " AND (id = :lcms OR lcms = :lcms)";
            } else {
                $where .= " AND lcms = :lcms";
            }
        }
        $user = $data ? sql_get([
            "table" => "admin",
            "where" => $where,
            "order" => "id DESC",
            "bind"  => [
                ":data" => $data,
                ":lcms" => $_L['ROOTID'],
            ]]) : [];
        return $user;
    }
    /**
     * @description: 图形验证码验证
     * @param string $code
     * @return bool
     */
    private static function checkCode($code = "")
    {
        global $_L;
        $code || ajaxout(0, "验证码错误");
        LOAD::sys_class("captcha");
        CAPTCHA::check($code) || ajaxout(0, "验证码错误");
    }
    /**
     * @description: 两步验证码验证
     * @param string $u2fa
     * @param string $code
     * @return bool
     */
    private static function check2fa($u2fa = "", $code = "")
    {
        global $_L;
        $code || ajaxout(0, "验证码错误");
        LOAD::plugin("2FA/TOTP");
        (new TOTP())->verifyCode($u2fa, $code) || ajaxout(0, "验证码错误");
    }
    /**
     * @description: 检测用户信息格式是否正确
     * @param array $opts
     * @param array $user
     * @return array
     */
    public static function checkFormat($opts, $user = [])
    {
        global $_L;
        $data = [];
        //如果修改账号
        if ($opts['name']) {
            self::isHave("name", $opts['name'], $user['id']);
            if (is_phone($opts['name'])) {
                if ($opts['mobile']) {
                    if ($opts['name'] != $opts['mobile']) {
                        ajaxout(0, "账号已存在");
                    }
                } elseif (
                    $user &&
                    $opts['name'] != $user['mobile']
                ) {
                    ajaxout(0, "账号已存在");
                }
            }
            if (is_email($opts['name'])) {
                if ($opts['email']) {
                    if ($opts['name'] != $opts['email']) {
                        ajaxout(0, "账号已存在");
                    }
                } elseif (
                    $user &&
                    $opts['name'] != $user['email']
                ) {
                    ajaxout(0, "账号已存在");
                }
            }
            $data['name'] = $opts['name'];
        } elseif ($user['name']) {
            $data['name'] = $user['name'];
        }
        //如果修改密码
        if ($opts['pass']) {
            $preg = $_L['developer']['rules']['password'];
            if (!preg_match($preg['pattern'], $opts['pass'])) {
                ajaxout(0, $preg['tips']);
            }
            $data['salt'] = randstr(8);
            $data['pass'] = md5("{$opts['pass']}{$data['salt']}");
        }
        if (
            $data['name'] &&
            $opts['pass'] &&
            $data['name'] == $opts['pass']
        ) {
            ajaxout(0, "账号和密码不能相同");
        }
        //如果修改手机号
        if (array_key_exists("mobile", $opts)) {
            if ($opts['mobile']) {
                self::isHave("mobile", $opts['mobile'], $user['id']);
            }
            $data['mobile'] = $opts['mobile'];
        }
        //如果修改邮箱
        if (array_key_exists("email", $opts)) {
            if ($opts['email']) {
                self::isHave("email", $opts['email'], $user['id']);
            }
            $data['email'] = $opts['email'];
        }
        //如果修改权限
        if (array_key_exists("level", $opts)) {
            $data['level'] = $opts['level'];
        }
        if (array_key_exists("type", $opts) && $opts['type']) {
            $data['type'] = $opts['type'];
        }
        //如果修改积分
        if (array_key_exists("points", $opts)) {
            if (in_string($opts['points'], "+")) {
                $points = $opts['points'] * 1 + $user['points'] * 1;
            } elseif (in_string($opts['points'], "-")) {
                $points = $opts['points'] * 1 + $user['points'] * 1;
                if ($points < 0) {
                    ajaxout(0, "积分不足");
                }
            } else {
                $points = $opts['points'];
                if ($points < 0) {
                    ajaxout(0, "积分不能小于0");
                }
            }
            $data['points'] = intval(round($points, 0));
        }
        //如果修改余额
        if (array_key_exists("balance", $opts)) {
            if (in_string($opts['balance'], "+")) {
                $balance = $opts['balance'] * 100 + $user['balance'] * 100;
            } elseif (in_string($opts['balance'], "-")) {
                $balance = $opts['balance'] * 100 + $user['balance'] * 100;
                if ($balance < 0) {
                    ajaxout(0, "余额不足");
                }
            } else {
                $balance = $opts['balance'] * 100;
                if ($balance < 0) {
                    ajaxout(0, "余额不能小于0");
                }
            }
            $balance         = intval(round($balance, 0));
            $data['balance'] = $balance / 100;
        }
        if ($opts['title']) {
            $data['title'] = $opts['title'];
        } elseif ($user['title']) {
            $data['title'] = $user['title'];
        } else {
            $data['title'] = $opts['name'];
        }
        if (!$data['title']) {
            ajaxout(0, "用户姓名不能为空");
        }
        if (array_key_exists("tuid", $opts)) {
            $data['tuid'] = $opts['tuid'] ?: 0;
        }
        if (array_key_exists("cate", $opts)) {
            $data['cate'] = $opts['cate'] ?: 0;
        }
        if (array_key_exists("status", $opts)) {
            $data['status'] = $opts['status'] ? 1 : 0;
        }
        if (array_key_exists("headimg", $opts)) {
            $data['headimg'] = $opts['headimg'];
        }
        if (array_key_exists("storage", $opts)) {
            $data['storage'] = abs(intval($opts['storage']));
        }
        if (array_key_exists("2fa", $opts)) {
            $data['2fa'] = $opts['2fa'];
        }
        if (array_key_exists("lasttime", $opts)) {
            $data['lasttime'] = $opts['lasttime'];
        }
        $data['lcms'] = array_key_exists("lcms", $opts) ? $opts['lcms'] : (array_key_exists("lcms", $user) ? $user['lcms'] : ($_L['ROOTID'] ?: 0));
        if ($user && $user['id']) {
            $data['id'] = $user['id'];
        } else {
            $data['addtime'] = datenow();
            unset($data['id']);
        }
        return $data;
    }
    /**
     * @description: 攻击检测
     * @param string $type
     * @param string $do
     * @param string $name
     * @return {*}
     */
    public static function checkAttack($type = "login", $do = "check", $name = "")
    {
        global $_L;
        $UCFG = self::UCFG()['login'];
        switch ($type) {
            case 'sms':
            case 'email':
                $icount = LCMS::ram("lcms_login_ip" . CLIENT_IP);
                $ncount = LCMS::ram("lcms_login_by{$name}");
                switch ($do) {
                    case 'update':
                        $icount = intval($icount ?: 0) + 1;
                        $ncount = intval($ncount ?: 0) + 1;
                        LCMS::ram("lcms_login_ip" . CLIENT_IP, $icount, 43200);
                        LCMS::ram("lcms_login_by{$name}", $ncount, 43200);
                        break;
                    default:
                        if ($icount >= 3 || $ncount >= 3) {
                            LCMS::notify("注册攻击通知", "<p>疑似遇到注册攻击，已被系统拦截。攻击IP：" . CLIENT_IP . "，注册信息：{$name}。</p><p>表单数据：<pre>" . json_encode_ex($_L['form']) . "</pre></p>", 86400);
                            ajaxout(0, "接口请求超限，请联系客服协助验证");
                        }
                        break;
                }
                break;
            case 'login':
                $rcount = LCMS::ram("lcms_login" . CLIENT_IP);
                $btime  = intval($UCFG['ban_time'] ?: 10);
                switch ($do) {
                    case 'update':
                        $rcount = intval($rcount ?: 0) + 1;
                        LCMS::ram("lcms_login" . CLIENT_IP, $rcount, $btime * 60);
                        break;
                    default:
                        $bcount = intval($UCFG['ban_count'] ?: 5);
                        if ($rcount >= $bcount) {
                            LCMS::notify("登录攻击通知", "<p>疑似遇到登录攻击，已被系统拦截。攻击IP：" . CLIENT_IP . "。</p><p>表单数据：<pre>" . json_encode_ex($_L['form']) . "</pre></p>", 3600);
                            ajaxout(0, "请{$btime}分钟后再试");
                        }
                        break;
                }
                break;
        }
    }
}
USERBASE::init();
