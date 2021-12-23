<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:15:23
 * @LastEditTime: 2021-12-23 15:41:45
 * @Description: 用户登陆
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        parent::__construct();
        $LF   = $_L['form'];
        $CFG  = $_L['config']['admin'];
        $USER = $_L['LCMSADMIN'];
        //初始化ROOTID
        $RID = $_L['ROOTID'] = $LF['rootid'] != null ? $LF['rootid'] : (SESSION::get("LOGINROOTID") ?: 0);
    }
    /**
     * @description: 登陆首页
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        //如果域名不正确，跳转到正确域名
        if ($CFG['domain'] && $CFG['domain'] != HTTP_HOST && !$LF['fixed']) {
            okinfo(str_replace(HTTP_HOST, $CFG['domain'], $_L['url']['now']));
        }
        //如果已经登陆，跳转到后台首页
        if ($USER && $USER['id'] && $USER['name']) {
            okinfo($_L['url']['admin']);
        }
        $UCFG = LCMS::config([
            "name" => "user",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => $RID == "0" ? true : $RID,
        ]) ?: [];
        SESSION::set("LOGINROOTID", $RID);
        //判断验证码模式
        if ($CFG['login_code']['type'] && $CFG['login_code']['type'] != "0" && stripos(HTTP_HOST, $CFG['login_code']['domain']) !== false) {
            switch ($CFG['login_code']['type']) {
                case 'luosimao':
                    load::plugin("Luosimao/captcha");
                    $captcha = new CAPTCHA($CFG['login_code']['luosimao']);
                    $yzcode  = $captcha->get();
                    break;
            }
        }
        if ($LF['action'] == "band") {
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
                "title" => "登陆 - {$CFG['title']}",
                "tab"   => [
                    ["title" => "账号登陆", "name" => "login"],
                ],
                "btn"   => "登陆",
            ];
            if ($UCFG['reg']['qrcode'] == '1') {
                $page['tab'][1] = [
                    "title" => "微信登陆",
                    "name"  => "qrcode",
                ];
            }
        }
        require LCMS::template("own/index");
    }
    /**
     * @description: 检测登陆状态
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        switch ($CFG['login_code']['type']) {
            case 'luosimao':
                //luosimao人机验证
                if ($LF['luotest_response']) {
                    load::plugin("Luosimao/captcha");
                    $YZ = new CAPTCHA($CFG['login_code']['luosimao']);
                    if (!$YZ->check($LF['luotest_response'])) {
                        ajaxout(0, "人机验证失败");
                    }
                } else {
                    ajaxout(0, "请进行人机验证");
                }
                break;
            default:
                //图形验证码验证
                if ($LF['code']) {
                    load::sys_class("captcha");
                    if (!CAPTCHA::check($LF['code'])) {
                        ajaxout(0, "验证码错误");
                    }
                } else {
                    ajaxout(0, "请输入验证码");
                };
                break;
        }
        //获取用户数据
        $admin = sql_get(["admin",
            "pass = :pass AND (name = :name OR email = :name OR mobile = :name)",
            "id DESC",
            [
                ":name" => $LF['name'],
                ":pass" => md5($LF['pass']),
            ],
        ]);
        //如果无用户数据
        if (!$admin) {
            ajaxout(0, "账号或密码错误");
        }
        //如果有用户数据
        if ($admin['status'] == '1') {
            if ($admin['lasttime'] > "0000-00-00 00:00:00" && $admin['lasttime'] < datenow()) {
                ajaxout(0, "此账户已到期");
            } else {
                $openid = SESSION::get("LOGINOPENID");
                if ($openid) {
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
                    ajaxout(1, "绑定成功", "goback");
                } else {
                    //登陆账号
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
                    ajaxout(1, "登录成功", $LF['go'] ?: $_L['url']['admin']);
                }
            }
        } else {
            ajaxout(0, "此账户已停用");
        }
    }
    /**
     * @description: 通过CID登陆
     * @param {*}
     * @return {*}
     */
    public function dologincid()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        if ($LF['cookie']) {
            $admin = ssl_decode($LF['cookie']);
            if ($admin) {
                $admin = json_decode($admin, true);
                SESSION::set("LCMSADMIN", $admin);
                ajaxout(1, "success");
            }
        }
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    /**
     * @description: 登陆状态检测
     * @param {*}
     * @return {*}
     */
    public function doping()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        if ($_L['LCMSADMIN']) {
            ajaxout(1, "登陆成功", $LF['go'] ?: $_L['url']['admin']);
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
        global $_L, $LF, $CFG, $USER, $RID;
        $code = ssl_encode(json_encode([
            "cid"  => $_COOKIE['LCMSCID'],
            "time" => time() + 180,
        ]));
        ajaxout(1, "success", "{$_L['url']['own_form']}index&c=qrcode&rootid={$RID}&code={$code}");
    }
    /**
     * @description: 退出登陆
     * @param {*}
     * @return {*}
     */
    public function dologinout()
    {
        global $_L, $LF, $CFG, $USER, $RID;
        $RID = $_L['LCMSADMIN']['lcms'];
        SESSION::del("LCMSADMIN");
        okinfo("?rootid={$RID}&n=login&go=" . urlencode($LF['go']));
    }
}
