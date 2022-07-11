<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-07-11 10:59:38
 * @LastEditTime: 2022-07-11 11:02:51
 * @Description: 登录注册设置
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class config extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    /**
     * @用户注册设置:
     * @param {type}
     * @return {type}
     */
    public function doindex()
    {
        global $_L, $LF, $LC;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "没有权限，禁止访问");
        }
        switch ($LF['action']) {
            case 'save':
                $level = explode("/", $LF['admin_level']);
                if ($level[0] !== "" && $level[1] !== "") {
                    $LC['reg']['lcms']  = $level[0];
                    $LC['reg']['level'] = $level[1];
                    LCMS::config([
                        "do"   => "save",
                        "type" => "sys",
                        "cate" => "admin",
                        "form" => $LC,
                    ]);
                    ajaxout(1, "保存成功");
                } else {
                    ajaxout(0, "保存失败，请选择默认权限");
                }
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "admin",
                ]);
                $form = [
                    "base" => [
                        ["layui" => "html", "title" => "登陆地址",
                            "name"   => "login_url",
                            "value"  => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login"],
                        ["layui" => "html", "title" => "注册地址",
                            "name"   => "login_url",
                            "value"  => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login&c=reg"],
                        ["layui" => "title", "title" => "三方登录"],
                        ["layui" => "des", "title" => "微信扫码登陆需安装《微信公众号管理》应用才可正常使用！<br/>QQ登录需申请接口 <a href='https://connect.qq.com/' target='_blank'>https://connect.qq.com/</a>。网站回调域填：{$_L['url']['web']['api']}core/plugin/Tencent/tpl/qqlogin.html"],
                        ["layui" => "radio", "title" => "微信扫码",
                            "name"   => "LC[reg][qrcode]",
                            "value"  => $config['reg']['qrcode'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0"],
                                ["title" => "启用", "value" => "1"],
                            ],
                        ],
                        ["layui" => "radio", "title" => "QQ登录",
                            "name"   => "LC[reg][qqlogin]",
                            "value"  => $config['reg']['qqlogin'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0", "tab" => "tab_qqlogin0"],
                                ["title" => "启用", "value" => "1", "tab" => "tab_qqlogin"],
                            ],
                        ],
                        ["layui" => "input", "title" => "APPID",
                            "name"   => "LC[reg][qqlogin_appid]",
                            "value"  => $config['reg']['qqlogin_appid'],
                            "cname"  => "hidden tab_qqlogin"],
                        ["layui" => "title", "title" => "注册设置"],
                        ["layui" => "radio", "title" => "用户注册",
                            "name"   => "LC[reg][on]",
                            "value"  => $config['reg']['on'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0", "tab" => "tab0"],
                                ["title" => "邮箱验证", "value" => "email", "tab" => "tab_email"],
                                ["title" => "手机号验证", "value" => "mobile", "tab" => "tab_mobile"],
                            ],
                        ],
                        ["layui" => "radio", "title" => "找回密码",
                            "name"   => "LC[reg][findpass]",
                            "value"  => $config['reg']['findpass'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0"],
                                ["title" => "开启", "value" => "1"],
                            ],
                            "tips"   => "需配置邮箱或短信接口！",
                        ],
                        ["layui" => "radio", "title" => "注册审核",
                            "name"   => "LC[reg][status]",
                            "value"  => $config['reg']['status'] ?? "0",
                            "radio"  => [
                                ["title" => "手动审核", "value" => "0"],
                                ["title" => "自动审核", "value" => "1"],
                            ],
                        ],
                        ["layui" => "input", "title" => "短信ID",
                            "name"   => "LC[reg][sms_tplcode]",
                            "value"  => $config['reg']['sms_tplcode'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件",
                        ],
                        ["layui" => "input", "title" => "短信签名",
                            "name"   => "LC[reg][sms_signname]",
                            "value"  => $config['reg']['sms_signname'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件",
                        ],
                        ["layui" => "selectN", "title" => "默认权限",
                            "name"   => "admin_level",
                            "value"  => "{$config['reg']['lcms']}/{$config['reg']['level']}",
                            "verify" => "required",
                            "url"    => "select&c=admin&action=admin-level",
                        ],
                        ["layui"   => "checkbox", "title" => "注册字段",
                            "checkbox" => [
                                ["title" => "姓名",
                                    "name"   => "LC[reg][input_title]",
                                    "value"  => $config['reg']['input_title']],
                            ],
                        ],
                        ["layui" => "title", "title" => "用户协议"],
                    ],
                    "btn"  => [
                        ["layui" => "btn", "title" => "立即保存"],
                    ],
                ];
                $readme = [
                    "user"    => [
                        ["layui" => "editor", "title" => "内容",
                            "name"   => "LC[readme][user]",
                            "value"  => $config['readme']['user']],
                    ],
                    "privacy" => [
                        ["layui" => "editor", "title" => "内容",
                            "name"   => "LC[readme][privacy]",
                            "value"  => $config['readme']['privacy']],
                    ],
                ];
                require LCMS::template("own/config/index");
                break;
        }
    }
}
