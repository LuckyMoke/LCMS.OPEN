<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-07-11 10:59:38
 * @LastEditTime: 2024-09-18 11:18:14
 * @Description: 登录注册设置
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class config extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC, $CFG;
        parent::__construct();
        $LF  = $_L['form'];
        $LC  = $LF['LC'];
        $CFG = LCMS::config([
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        if (!LCMS::SUPER() && $CFG['login']['mode'] == 1) {
            LCMS::X(403, "此功能仅超级管理员可用");
        }
    }
    /**
     * @用户注册设置:
     * @param {type}
     * @return {type}
     */
    public function doindex()
    {
        global $_L, $LF, $LC, $CFG;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "此功能仅管理员可用");
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
                $config = LCMS::SUPER() ? $CFG : LCMS::config([
                    "type" => "sys",
                    "cate" => "admin",
                ]);
                $form = [
                    "base" => [
                        ["layui" => "radio", "title" => "登录模式",
                            "name"   => "LC[login][mode]",
                            "value"  => $config['login']['mode'] ?? 1,
                            "radio"  => [
                                ["title" => "总平台", "value" => 1],
                                ["title" => "子平台", "value" => 0],
                            ]],
                        ["layui" => "radio", "title" => "单点登录",
                            "name"   => "LC[login][jwt]",
                            "value"  => $config['login']['jwt'] ?? 0,
                            "radio"  => [
                                ["title" => "开启", "value" => 1],
                                ["title" => "关闭", "value" => 0],
                            ]],
                        ["layui" => "html", "title" => "登录地址",
                            "name"   => "login_url",
                            "value"  => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login",
                            "copy"   => true],
                        ["layui" => "html", "title" => "注册地址",
                            "name"   => "login_url",
                            "value"  => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login&c=reg",
                            "copy"   => true],
                        ["layui" => "title", "title" => "三方登录"],
                        ["layui" => "des", "title" => "微信登录需安装《微信公众号管理》应用才可正常使用！如需关注公众号，需要在应用“扫码事件”中添加一个<code>sys@login</code><br/>QQ登录需申请接口 <a href='https://connect.qq.com/' target='_blank'>https://connect.qq.com/</a>。网站回调域填：<a class='lcms-form-copy'>{$config['reg']['qqlogin_domain']}core/plugin/Tencent/tpl/qqlogin.html</a>"],
                        ["layui" => "radio", "title" => "微信登录",
                            "name"   => "LC[reg][qrcode]",
                            "value"  => $config['reg']['qrcode'] ?? 0,
                            "radio"  => [
                                ["title" => "关闭", "value" => 0],
                                ["title" => "普通扫码", "value" => 1],
                                ["title" => "关注公众号", "value" => 2],
                            ],
                        ],
                        ["layui" => "radio", "title" => "QQ登录",
                            "name"   => "LC[reg][qqlogin]",
                            "value"  => $config['reg']['qqlogin'] ?? 0,
                            "radio"  => [
                                ["title" => "关闭", "value" => 0, "tab" => "tab_qqlogin0"],
                                ["title" => "启用", "value" => 1, "tab" => "tab_qqlogin"],
                            ],
                        ],
                        ["layui" => "input", "title" => "QQ回调域",
                            "name"   => "LC[reg][qqlogin_domain]",
                            "value"  => $config['reg']['qqlogin_domain'] ?: $_L['url']['web']['api'],
                            "cname"  => "hidden tab_qqlogin",
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
                        ["layui" => "input", "title" => "短信模板ID",
                            "name"   => "LC[reg][sms_tplcode]",
                            "value"  => $config['reg']['sms_tplcode'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件，然后在短信模板中获取模板ID，必须使用只有1个验证码参数的模板。",
                        ],
                        ["layui" => "input", "title" => "短信签名",
                            "name"   => "LC[reg][sms_signname]",
                            "value"  => $config['reg']['sms_signname'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件",
                        ],
                        ["layui" => "tags", "title" => "禁止号段",
                            "name"   => "LC[reg][sms_black]",
                            "value"  => $config['reg']['sms_black'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "禁止注册的手机号段",
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
                if (!LCMS::SUPER() && $CFG['login']['mode'] < 1) {
                    unset($form['base'][0]);
                }
                require LCMS::template("own/config/index");
                break;
        }
    }
}
