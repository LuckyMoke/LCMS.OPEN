<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 15:03:35
 * @LastEditTime: 2023-09-21 11:56:47
 * @Description: 用户注册
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class("captcha");
load::own_class('pub');
class reg extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        parent::__construct();
        $LF   = $_L['form'];
        $UCFG = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        if ($UCFG['reg']['mode'] < 1) {
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
        if ($RID != 0 && !$UCFG) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        if (!in_array($UCFG['reg']['on'], ["mobile", "email"])) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        $PLG = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "plugin",
            "lcms" => $RID == 0 ? true : $RID,
        ]);
        SESSION::set("LOGINROOTID", $RID);
    }
    /**
     * @description: 注册页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        $admin = $_L['LCMSADMIN'];
        if ($admin && $admin['id'] && $admin['name']) {
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        $page = [
            "title" => "注册 - {$_L['config']['admin']['title']}",
            "tab"   => [
                ["title" => "用户注册", "name" => "reg"],
            ],
        ];
        $tplpath = is_dir(PATH_APP_NOW . "admin/tpl/custom") ? "custom" : "default";
        require LCMS::template("own/{$tplpath}/reg");
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
        $number = $LF['value'][$LF['action']];
        //判断验证码是否正确
        CAPTCHA::check($LF['code']) || ajaxout(0, "验证码错误");
        //判断是否已发送过验证码
        $time = SESSION::get("LOGINCODETIME");
        if ($time > time()) {
            ajaxout(0, "请 " . ($time - time()) . " 秒后再试");
        }
        //判断账号是否存在
        PUB::ishave($LF['action'], $number);
        $text = randstr(6, "num");
        switch ($LF['action']) {
            case 'email':
                $number = $LF['value']['email'];
                PUB::sendemail($number, $text);
                break;
            case 'mobile':
                $number = $LF['value']['mobile'];
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
     * @description: 注册流程
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $UCFG, $PLG, $RID;
        //判断短信邮箱验证码是否正确
        $code = SESSION::get("LOGINSENDCODE");
        if (!$code || $code != strtoupper($LF['code'])) {
            ajaxout(0, "验证码错误");
        }
        //检测账号是否合规
        PUB::ishave("name", $LF['name']);
        //检测密码是否合规
        PUB::ishave("pass", $LF['pass']);
        //生成盐
        $salt  = randstr(8);
        $admin = [
            "name"    => $LF['name'],
            "title"   => $LF['title'] ?: $LF['name'],
            "pass"    => md5("{$LF['pass']}{$salt}"),
            "salt"    => $salt,
            "status"  => $UCFG['reg']['status'],
            "addtime" => datenow(),
            "type"    => $UCFG['reg']['level'],
            "lcms"    => $UCFG['reg']['lcms'],
        ];
        $number = SESSION::get("LOGINNUMBER");
        if (PUB::is_email($number)) {
            //如果是邮件验证
            if ($number) {
                $admin['email'] = $number;
            } else {
                ajaxout(0, "缺少邮箱地址");
            }
        } else {
            //如果是手机号验证
            if ($number) {
                $admin['mobile'] = $number;
            } else {
                ajaxout(0, "缺少手机号");
            }
        }
        //删除缓存数据
        SESSION::del("LOGINSENDCODE");
        SESSION::del("LOGINNUMBER");
        //写入数据库
        sql_insert(["admin", $admin]);
        if (sql_error()) {
            LCMS::log([
                "user" => $admin['name'],
                "type" => "login",
                "info" => "用户注册-注册失败-" . sql_error(),
            ]);
            ajaxout(0, "注册失败，请联系管理员");
        } else {
            LCMS::log([
                "user" => $admin['name'],
                "type" => "login",
                "info" => "用户注册-注册成功",
            ]);
            ajaxout(1, "注册成功，请登陆", "{$_L['url']['own']}rootid={$RID}&n=login");
        }
    }
}
