<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:15:23
 * @LastEditTime: 2026-02-09 01:26:40
 * @Description: 用户登录
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class("adminbase");
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $UCFG, $USER;
        parent::__construct();
        $LF   = $_L['form'];
        $USER = $_L['LCMSADMIN'];
        LOAD::sys_class("userbase");
        $UCFG = USERBASE::UCFG();
    }
    /**
     * @description: 登录首页
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG, $USER;
        //基础目录权限检测
        if (!getdirpower("/cache")) {
            LCMS::X(403, "<code>/cache</code>目录无读写权限");
        }
        //如果已经登录，跳转到后台首页
        if ($USER && $USER['id'] && $USER['name']) {
            USERBASE::createCookie($USER);
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        //如果有登录cookie，自动登录
        if (
            $_L['cookie']['LCMSUSERJWT'] &&
            USERBASE::login([
                "by"  => "cookie",
                "jwt" => $_L['cookie']['LCMSUSERJWT'],
            ])
        ) {
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        switch ($LF['action']) {
            case 'band':
                $page = [
                    "title" => "账号绑定",
                    "tab"   => [[
                        "title" => "账号绑定",
                        "by"    => "band",
                    ]],
                    "btn"   => "绑定",
                ];
                break;
            default:
                $page = [
                    "title" => "登录 - {$_L['config']['admin']['title']}",
                    "tab" => [[
                        "title" => "账号登录",
                        "by"    => "name",
                    ]],
                    "btn" => "登录",
                ];
                if ($UCFG['reg']['qrcode'] > 0) {
                    $page['tab'] = array_merge($page['tab'], [[
                        "title" => "微信登录",
                        "by"    => "wechat",
                    ]]);
                }
                if ($UCFG['reg']['qqlogin'] > 0) {
                    $page['tab'] = array_merge($page['tab'], [[
                        "title" => "QQ登录",
                        "by"    => "qq",
                    ]]);
                }
                break;
        }
        //设置登录TOKEN
        USERBASE::token("set");
        if (is_dir(PATH_APP_NOW . "admin/tpl/custom")) {
            require LCMS::template("own/custom/index");
        } else {
            require LCMS::template("own/default/index");
        }

    }
    /**
     * @description: 检测登录状态
     * @param {*}
     * @return {*}
     */
    public function dologin()
    {
        global $_L, $LF, $UCFG, $USER;
        switch ($LF['by']) {
            case 'jwt':
                $user = USERBASE::login([
                    "by"  => "jwt",
                    "jwt" => $LF['jwt'],
                ]);
                ajaxout(1, "登录成功", "", $user);
                break;
            case 'rsa':
                USERBASE::checkAttack("login");
                $cfg = $_L['config']['admin'];
                if (
                    $cfg['loginbyrsa'] != 1 ||
                    !$cfg['loginprivatekey']
                ) {
                    LCMS::X(403, "未开启此功能");
                }
                $user = USERBASE::login([
                    "by"         => "rsa",
                    "token"      => $LF['token'],
                    "privatekey" => $cfg['loginprivatekey'],
                ]);
                $user || LCMS::X(403, "登录失败");
                okinfo($_L['url']['admin']);
                break;
            default:
                //检查登录签名
                USERBASE::token("check");
                //解密用户名
                $LF['name'] = $_L['form']['name'] = USERBASE::token("decode", $LF['name']);
                //解密密码
                $LF['pass'] = USERBASE::token("decode", $LF['pass']);
                $user       = USERBASE::login([
                    "by"   => "name",
                    "name" => $LF['name'],
                    "pass" => $LF['pass'],
                    "code" => $LF['code'],
                    "2fa"  => $LF['2fa'],
                    "band" => $LF['band'],
                    "go"   => $_L['go'],
                ]);
                if ($user) {
                    //清除登录签名
                    USERBASE::token("clear");
                    if ($LF['band']) {
                        ajaxout(1, "绑定成功", "goback");
                    }
                    ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin']);
                }
                break;
        }
    }
    /**
     * @description: 登录状态检测
     * @param {*}
     * @return {*}
     */
    public function doping()
    {
        global $_L, $LF, $UCFG, $USER;
        if ($USER) {
            USERBASE::createCookie($USER);
            ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin'], [
                "cid" => SESSION::getid(),
            ]);
        }
        ajaxout(0, "failed");
    }
    /**
     * @description: 生成第三方登录二维码
     * @return {*}
     */
    public function doqrcode()
    {
        global $_L, $LF, $UCFG, $USER;
        $url = USERBASE::createQrcode($LF['action']);
        ajaxout(1, "success", "", $url);
    }
    /**
     * @description: 获取用户协议
     * @param {*}
     * @return {*}
     */
    public function doreadme()
    {
        global $_L, $LF, $UCFG, $USER;
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
     * @description: 退出登录
     * @param {*}
     * @return {*}
     */
    public function dologinout()
    {
        global $_L, $LF, $UCFG, $USER;
        $go = USERBASE::loginout();
        if (
            $_L['cookie']['LCMSUSERCATE'] > 0 &&
            $UCFG['login']['url']
        ) {
            setcookie("LCMSUSERCATE", "", [
                "expires"  => time(),
                "path"     => "/",
                "secure"   => false,
                "httponly" => true,
            ]);
            $go = $UCFG['login']['url'];
        }
        okinfo($go);
    }
}
