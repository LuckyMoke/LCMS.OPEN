<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-28 15:03:35
 * @LastEditTime: 2021-10-31 17:17:35
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
        global $_L, $LF, $UCFG, $RID, $CID, $WX, $WUSER;
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
        if ($UCFG['reg']['qrcode'] != "1") {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
        SESSION::set("LOGINROOTID", $RID);
        load::plugin("WeChat/OA");
        $WX = new OA();
        if ($LF['code']) {
            $code = json_decode(ssl_decode($LF['code']), true);
            if ($code && $code['time'] > time()) {
                $CID = $code['cid'];
                SESSION::set("LOGINCID", $CID);
            } else {
                $this->msg([
                    "icon"  => "warn",
                    "title" => "二维码已过期",
                    "desc"  => "请重新获取二维码扫描",
                ]);
            }
        } else {
            $CID = SESSION::get("LOGINCID");
        }
        if (!$CID) {
            $this->msg([
                "icon"  => "warn",
                "title" => "禁止访问",
                "desc"  => "Forbidden",
            ]);
        }
        $WUSER = $WX->userinfo();
        SESSION::set("LOGINOPENID", $WUSER['openid']);
    }
    /**
     * @description: 扫码登陆页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $UCFG, $RID, $CID, $WUSER;
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
        require LCMS::template("own/qrcode");
    }
    /**
     * @description: 登陆操作
     * @param {*}
     * @return {*}
     */
    public function dologin()
    {
        global $_L, $LF, $UCFG, $RID, $CID;
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
                    $this->msg([
                        "icon"  => "warn",
                        "title" => "登陆失败",
                        "desc"  => "此账号已到期",
                    ]);
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
                    $result = HTTP::get("{$_L['url']['own_form']}logincid&c=index&rootsid={$CID}&cookie=" . ssl_encode(json_encode($admin)));
                    $result = json_decode($result, true);
                    if ($result['code'] == "1") {
                        $this->msg([
                            "title" => "登陆成功",
                            "desc"  => "请返回网页端查看",
                        ]);
                    } else {
                        $this->msg([
                            "icon"  => "warn",
                            "title" => "登陆失败",
                        ]);
                    }
                }
            } else {
                $this->msg([
                    "icon"  => "warn",
                    "title" => "登陆失败",
                    "desc"  => "账号已停用",
                ]);
            }
        } else {
            $this->msg([
                "icon"  => "warn",
                "title" => "登陆失败",
                "desc"  => "请先注册账号绑定",
            ]);
        }
    }
    /**
     * @description: 解绑账号
     * @param {*}
     * @return {*}
     */
    public function dounband()
    {
        global $_L, $LF, $UCFG, $RID, $CID, $WUSER;
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
            $this->msg([
                "title" => "解绑成功",
            ]);
        } else {
            $this->msg([
                "icon"  => "warn",
                "title" => "解绑失败",
            ]);
        }
    }
    /**
     * @description: 输出信息页面
     * @param {*} $page
     * @return {*}
     */
    private function msg($page = [])
    {
        global $_L, $LF, $UCFG, $RID, $CID, $WX;
        $WX->page_msg([
            "icon"  => $page['icon'],
            "title" => $page['title'],
            "desc"  => $page['desc'],
        ]);
    }
}
