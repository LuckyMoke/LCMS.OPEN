<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 15:03:35
 * @LastEditTime: 2025-04-16 15:14:14
 * @Description: 扫码登录
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class("adminbase");
class qrcode extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $UCFG, $OPENID;
        parent::__construct();
        $LF = $_L['form'];
        LOAD::sys_class("userbase");
        $UCFG   = USERBASE::UCFG();
        $OPENID = USERBASE::getOpenid($LF['action']);
    }
    /**
     * @description: 扫码登录页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG, $OPENID;
        if ($UCFG['reg'][$LF['action']] < 1) {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
        $page = [
            "title" => "账号列表",
        ];
        if (is_dir(PATH_APP_NOW . "admin/tpl/custom")) {
            require LCMS::template("own/custom/qrcode");
        } else {
            require LCMS::template("own/default/qrcode");
        }
    }
    /**
     * @description: 获取用户列表
     * @return {*}
     */
    public function dousers()
    {
        global $_L, $LF, $OPENID;
        $users = USERBASE::getBand($OPENID);
        foreach ($users as $index => $user) {
            $users[$index] = [
                "id"     => $user['id'],
                "name"   => $user['name'],
                "title"  => $user['title'],
                "mobile" => strstar($user['mobile'], 3, 4),
                "email"  => $user['email'],
                "token"  => ssl_encode_gzip($user['name']),
            ];
        }
        ajaxout(1, "success", "", $users);
    }
    /**
     * @description: 登录操作
     * @param {*}
     * @return {*}
     */
    public function dologin()
    {
        global $_L, $LF, $OPENID;
        $LF['token'] = $LF['token'] ? ssl_decode_gzip($LF['token']) : "";
        $LF['token'] || ajaxout(0, "登录失败");
        if (USERBASE::login([
            "by"     => $LF['action'] ?: "qrcode",
            "name"   => $LF['token'],
            "openid" => $OPENID,
        ])) {
            ajaxout(1, "登录成功");
        }
    }
    /**
     * @description: 解绑账号
     * @param {*}
     * @return {*}
     */
    public function dounband()
    {
        global $_L, $LF, $OPENID;
        if (USERBASE::unBand([
            "name"   => $LF['name'],
            "openid" => $OPENID,
        ])) {
            ajaxout(1, "解绑成功");
        }
    }
}
