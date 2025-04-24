<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 18:49:56
 * @LastEditTime: 2025-04-16 14:38:54
 * @Description: 找回密码
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class("adminbase");
class find extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $UCFG;
        parent::__construct();
        $LF = $_L['form'];
        LOAD::sys_class("userbase");
        $UCFG = USERBASE::UCFG();
        if (!in_array($UCFG['reg']['on'], [
            "mobile", "email",
        ])) {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
        if ($UCFG['reg']['findpass'] <= 0) {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
    }
    /**
     * @description: 找回密码页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG;
        switch ($LF['action']) {
            case 'reset':
                $to   = SESSION::get("lcms_login_find");
                $page = [
                    "title" => "重设密码 - {$_L['config']['admin']['title']}",
                    "tab"   => [[
                        "title" => "重设密码",
                        "by"    => "reset",
                    ]],
                ];
                if (is_dir(PATH_APP_NOW . "admin/tpl/custom")) {
                    require LCMS::template("own/custom/reset");
                } else {
                    require LCMS::template("own/default/reset");
                }
                break;
            default:
                $page = [
                    "title" => "找回密码 - {$_L['config']['admin']['title']}",
                    "tab"   => [[
                        "title" => "找回密码",
                        "by"    => "find",
                    ]],
                ];
                if (is_dir(PATH_APP_NOW . "admin/tpl/custom")) {
                    require LCMS::template("own/custom/find");
                } else {
                    require LCMS::template("own/default/find");
                }
                break;
        }
    }
    /**
     * @description: 发送验证码
     * @param {*}
     * @return {*}
     */
    public function dosendcode()
    {
        global $_L, $LF, $UCFG;
        //检测账号是否存在
        USERBASE::checkUser([
            "name" => $LF['to'],
        ]);
        $vcode = USERBASE::sendCode([
            "by"   => $LF['by'],
            "to"   => $LF['to'],
            "code" => $LF['code'],
        ]);
        $vcode && ajaxout(1, "验证码已发送");
    }
    /**
     * @description: 验证码检测
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $UCFG;
        $to = USERBASE::checkSendCode($LF['vcode']);
        if ($to) {
            SESSION::set("lcms_login_find", $to);
            $token = ssl_encode_gzip($to);
            ajaxout(1, "验证成功", "{$_L['url']['own']}rootid={$_L['ROOTID']}&n=login&c=find&action=reset&token={$token}");
        }
    }
    /**
     * @description: 密码重置页面
     * @param {*}
     * @return {*}
     */
    public function doreset()
    {
        global $_L, $LF, $UCFG;
        $to          = SESSION::get("lcms_login_find");
        $LF['token'] = $LF['token'] ? ssl_decode_gzip($LF['token']) : "";
        if (
            !$LF['token'] ||
            !$to ||
            $LF['token'] != $to
        ) {
            ajaxout(0, "请重新找回密码");
        }
        if (USERBASE::reset([
            "to"   => $to,
            "pass" => $LF['pass'],
        ])) {
            SESSION::del("lcms_login_find");
            ajaxout(1, "密码重置成功", "{$_L['url']['own']}rootid={$_L['ROOTID']}&n=login");
        }
    }
}
