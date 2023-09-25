<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 15:03:35
 * @LastEditTime: 2023-09-21 12:02:34
 * @Description: 扫码登陆
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class("captcha");
load::own_class('pub');
class qrcode extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $WUSER;
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
        if ($UCFG['reg'][$LF['name']] < 1) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        SESSION::set("LOGINROOTID", $RID);
        SESSION::get("LOGINQRCODETIME") < time() && LCMS::X(403, "请重新扫码");
        SESSION::get("LCMSADMIN") && LCMS::Y(200, "用户已登录");
        switch ($LF['name']) {
            case 'qrcode':
                load::plugin("WeChat/OA");
                $WX    = new OA();
                $WUSER = $WX->openid();
                break;
            case 'qqlogin':
                load::plugin("Tencent/QQConnect");
                $QQ = new QQConnect([
                    "appid"   => $UCFG['reg']['qqlogin_appid'],
                    "display" => "pc",
                ]);
                $openid = $QQ->openid();
                $openid || LCMS::X(403, "登录失败");
                $WUSER = [
                    "openid" => "QQ{$openid}",
                ];
                break;
        }
        $WUSER['openid'] || LCMS::X(403, "登录失败");
        SESSION::set("LOGINOPENID", $WUSER['openid']);
    }
    /**
     * @description: 扫码登陆页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $WUSER;
        $ids = sql_get(["admin_band",
            "openid = :openid",
            "id ASC", [
                ":openid" => $WUSER['openid'],
            ], "GROUP_CONCAT(aid) ids"])['ids'];
        $admin = $ids ? sql_getall(["admin",
            "id IN ({$ids}) AND status = 1 AND (lasttime IS NULL OR lasttime > :lasttime)",
            "", [
                ":lasttime" => datenow(),
            ],
        ]) : [];
        $page = [
            "title" => "账号列表",
        ];
        $tplpath = is_dir(PATH_APP_NOW . "admin/tpl/custom") ? "custom" : "default";
        require LCMS::template("own/{$tplpath}/qrcode");
    }
    /**
     * @description: 登陆操作
     * @param {*}
     * @return {*}
     */
    public function dologin()
    {
        global $_L, $LF, $WUSER;
        $admin = sql_get(["admin",
            "name = :name OR email = :name OR mobile = :name",
            "id DESC", [
                ":name" => $LF['account'],
            ],
        ]);
        if ($admin) {
            //todo验证账号是否绑定
            if ($admin['status'] == '1') {
                if ($admin['lasttime'] > "0000-00-00 00:00:00" && $admin['lasttime'] < datenow()) {
                    LCMS::X(403, "登陆失败<br/>此账号已到期");
                } else {
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
                        "info" => "登陆成功-第三方登陆",
                    ]);
                    LCMS::Y(200, "登陆成功<br/>请返回网页端查看", "close");
                }
            } else {
                LCMS::X(403, "登陆失败<br/>此账号已停用");
            }
        } else {
            LCMS::X(403, "登陆失败<br/>请先注册账号绑定");
        }
    }
    /**
     * @description: 解绑账号
     * @param {*}
     * @return {*}
     */
    public function dounband()
    {
        global $_L, $LF, $WUSER;
        $admin = sql_get(["admin",
            "name = :name OR email = :name OR mobile = :name",
            "id DESC", [
                ":name" => $LF['account'],
            ],
        ]);
        if ($admin) {
            sql_delete(["admin_band",
                "openid = :openid AND aid = :aid", [
                    ":openid" => $WUSER['openid'],
                    ":aid"    => $admin['id'],
                ],
            ]);
            LCMS::Y(200, "解绑成功");
        } else {
            LCMS::X(403, "解绑失败");
        }
    }
}
