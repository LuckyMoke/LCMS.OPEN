<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 15:03:35
 * @LastEditTime: 2026-02-08 23:24:32
 * @Description: 用户注册
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class("adminbase");
class reg extends adminbase
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
    }
    /**
     * @description: 注册页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG;
        if ($_L['LCMSADMIN']) {
            USERBASE::createCookie($_L['LCMSADMIN']);
            okinfo($LF['go'] ?: $_L['url']['admin']);
        }
        $page = [
            "title" => "注册 - {$_L['config']['admin']['title']}",
            "tab"   => [[
                "title" => "用户注册",
                "by"    => "reg",
            ]],
        ];
        if (is_dir(PATH_APP_NOW . "admin/tpl/custom")) {
            require LCMS::template("own/custom/reg");
        } else {
            require LCMS::template("own/default/reg");
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
        //检测用户信息格式
        USERBASE::checkFormat($LF);
        //发送验证码
        $vcode = USERBASE::sendCode([
            "by"   => $LF['by'],
            "to"   => $LF['to'],
            "code" => $LF['code'],
        ]);
        $vcode && ajaxout(1, "验证码已发送");
    }
    /**
     * @description: 注册流程
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $UCFG;
        //添加用户信息
        $user = USERBASE::register([
            "by"     => "vcode",
            "name"   => $LF['name'],
            "title"  => $LF['title'],
            "pass"   => $LF['pass'],
            "status" => $UCFG['reg']['status'],
            "type"   => $UCFG['reg']['level'],
            "cate"   => 1,
            "lcms"   => $UCFG['reg']['lcms'],
            "vcode"  => $LF['vcode'],
        ]);
        if ($user['status'] > 0) {
            //注册成功登录
            USERBASE::loginSuccess($user, "注册登录");
            ajaxout(1, "注册成功", $_L['url']['admin']);
        } else {
            ajaxout(1, "注册成功，请等待管理员审核");
        }

    }
}
