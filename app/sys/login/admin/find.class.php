<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 18:49:56
 * @LastEditTime: 2021-10-30 10:30:39
 * @Description: 找回密码
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class("captcha");
load::own_class('pub');
class find extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        parent::__construct();
        $LF   = $_L['form'];
        $RID  = $_L['ROOTID']  = $LF['rootid'] != null ? $LF['rootid'] : (SESSION::get("LOGINROOTID") ?: 0);
        $UCFG = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => $RID == "0" ? true : $RID,
        ]);
        if ($RID != 0 && !$UCFG) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        if ($UCFG['reg']['on'] == null || $UCFG['reg']['on'] == "0") {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        $PLG = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "plugin",
            "lcms" => $RID == "0" ? true : $RID,
        ]);
        SESSION::set("LOGINROOTID", $RID);
    }
    /**
     * @description: 找回密码页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        if ($UCFG['reg']['findpass'] > "0") {
            $page = [
                "title" => "找回密码 - {$_L['config']['admin']['title']}",
                "tab"   => [
                    ["title" => "找回密码", "name" => "find"],
                ],
            ];
            require LCMS::template("own/find");
        } else {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
    }
    /**
     * @description: 发送验证码
     * @param {*}
     * @return {*}
     */
    public function dosendcode()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        //得到号码
        $number = $LF[$LF['action']];
        //判断验证码是否正确
        CAPTCHA::check($LF['code']) || ajaxout(0, "验证码错误");
        //判断是否已发送过验证码
        $time = SESSION::get("LOGINCODETIME");
        if ($time > time()) {
            ajaxout(0, "请 " . ($time - time()) . " 秒后再试");
        }
        //判断账号是否存在
        PUB::ishave($LF['action'], $number, false);
        $text = randstr(6, "num");
        switch ($LF['action']) {
            case 'email':
                PUB::sendemail($number, $text);
                break;
            case 'mobile':
                if ($UCFG['reg']['sms_tplcode']) {
                    PUB::sendsms($number, $text);
                } else {
                    ajaxout(0, "未开启短信功能");
                }
                break;
        }
        //缓存验证号码
        SESSION::set("LOGINNUMBER", $number);
        //缓存验证码
        SESSION::set("LOGINSENDCODE", $text);
        //缓存验证时间
        SESSION::set("LOGINCODETIME", time() + 120);
        ajaxout(1, "验证码已发送");
    }
    /**
     * @description: 验证码检测
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        //验证码验证
        $code = SESSION::get("LOGINSENDCODE");
        if (!$code || $code != strtoupper($LF['code'])) {
            ajaxout(0, "验证码错误");
        }
        //读取号码
        $number = SESSION::get("LOGINNUMBER");
        //删除缓存
        SESSION::del("LOGINNUMBER");
        SESSION::del("LOGINSENDCODE");
        ajaxout(1, "验证成功", "?rootid={$RID}&n=login&c=find&a=reset&code=" . ssl_encode($number));
    }
    /**
     * @description: 密码重置页面
     * @param {*}
     * @return {*}
     */
    public function doreset()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        //判断页面权限
        $number = $LF['code'] ? ssl_decode($LF['code']) : "";
        if (!$number) {
            LCMS::X("404", "未找到页面");
        }
        if (!PUB::is_email($number) && !is_phone($number)) {
            okinfo("?rootid={$RID}&n=login&c=find");
        }
        sql_get(["admin",
            "email = :number OR mobile = :number",
            "id DESC", [
                ":number" => $number,
            ],
        ]) || LCMS::X(404, "未找到页面");
        switch ($LF['action']) {
            case 'save':
                if (mb_strlen($LF['pass'], "UTF8") < 6) {
                    ajaxout(0, "密码不能少于6位");
                }
                sql_update(["admin", [
                    "pass" => md5($LF['pass']),
                ], "email = :number OR mobile = :number", [
                    ":number" => $number,
                ]]);
                ajaxout(1, "密码设置成功", "?rootid={$RID}&n=login");
                break;
            default:
                $page = [
                    "title" => "重设密码 - {$_L['config']['admin']['title']}",
                    "tab"   => [
                        ["title" => "重设密码", "name" => "reset"],
                    ],
                ];
                require LCMS::template("own/reset");
                break;
        }
    }
}
