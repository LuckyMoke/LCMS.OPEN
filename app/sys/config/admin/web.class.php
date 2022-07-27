<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2022-07-23 15:39:23
 * @Description: 基本设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */

defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class web extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doplugin()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                ajaxout(1, "保存成功");
                break;
            case 'test_eamil':
                $email = $_L['form']['email'];
                if (is_email($email)) {
                    load::sys_class("email");
                    $result = EMAIL::send([
                        "TO"    => $email,
                        "Title" => "邮件发送测试",
                        "Body"  => "恭喜您，邮件服务配置成功！",
                    ]);
                }
                if ($result['code'] == "1") {
                    ajaxout(1, "邮件服务配置成功");
                } else {
                    ajaxout(0, "邮件服务配置失败！{$result['msg']}");
                }
                break;
            default:
                $plugin = LCMS::config([
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                $PLG         = $plugin['email'] ?: [];
                $PLG['smtp'] = is_array($PLG['smtp']) ? $PLG['smtp'] : [];
                $email       = [
                    ["layui" => "title", "title" => "邮箱配置"],
                    ["layui" => "radio", "title" => "邮箱接口",
                        "name"   => "LC[email][type]",
                        "value"  => $PLG['type'] ?: "smtp",
                        "radio"  => [
                            ["title" => "SMTP", "value" => "smtp", "tab" => "email-smtp"],
                            ["title" => "阿里云", "value" => "aliyun", "tab" => "email-aliyun"],
                            ["title" => "腾讯云", "value" => "tencent", "tab" => "email-tencent"],
                        ]],
                    ["layui" => "input", "title" => "发件人",
                        "name"   => "LC[email][smtp][Alias]",
                        "value"  => $PLG['smtp']['Alias'],
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "input", "title" => "发件地址",
                        "name"   => "LC[email][smtp][From]",
                        "value"  => $PLG['smtp']['From'],
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "input", "title" => "回信地址",
                        "name"   => "LC[email][smtp][Reply]",
                        "value"  => $PLG['smtp']['Reply'],
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "input", "title" => "SMTP服务器",
                        "name"   => "LC[email][smtp][Smtp]",
                        "value"  => $PLG['smtp']['Smtp'],
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "input", "title" => "SMTP密码",
                        "name"   => "LC[email][smtp][Pass]",
                        "value"  => $PLG['smtp']['Pass'],
                        "type"   => "password",
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "on", "title" => "TLS/SSL",
                        "name"   => "LC[email][smtp][SSL]",
                        "value"  => $PLG['smtp']['SSL'] ?? 1,
                        "text"   => "SSL|TLS",
                        "tips"   => "一般情况下<br>SSL端口为465，TLS端口为25",
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "input", "title" => "端口",
                        "name"   => "LC[email][smtp][Port]",
                        "value"  => $PLG['smtp']['Port'] ?: 465,
                        "tips"   => "一般情况下<br>SSL端口为465，TLS端口为25",
                        "cname"  => "hidden email-smtp"],
                    ["layui" => "des", "title" => "阿里云邮件推送开通地址&nbsp;&nbsp;<a href='https://www.aliyun.com/product/directmail?userCode=kabw9nx2&tag=share_component&share_source=copy_link' target='_blank'>[点击访问] https://www.aliyun.com/product/directmail</a>", "cname" => "hidden email-aliyun"],
                    ["layui" => "input", "title" => "AccessKeyId",
                        "name"   => "LC[email][aliyun][AccessKeyId]",
                        "value"  => $PLG['aliyun']['AccessKeyId'],
                        "cname"  => "hidden email-aliyun"],
                    ["layui" => "input",
                        "title"  => "AccessKeySecret",
                        "name"   => "LC[email][aliyun][AccessKeySecret]",
                        "value"  => $PLG['aliyun']['AccessKeySecret'],
                        "cname"  => "hidden email-aliyun"],
                    ["layui" => "input", "title" => "发件人",
                        "name"   => "LC[email][aliyun][Alias]",
                        "value"  => $PLG['aliyun']['Alias'],
                        "cname"  => "hidden email-aliyun"],
                    ["layui" => "input", "title" => "发件地址",
                        "name"   => "LC[email][aliyun][From]",
                        "value"  => $PLG['aliyun']['From'],
                        "cname"  => "hidden email-aliyun"],
                    ["layui" => "input", "title" => "回信地址",
                        "name"   => "LC[email][aliyun][Reply]",
                        "value"  => $PLG['aliyun']['Reply'],
                        "cname"  => "hidden email-aliyun"],
                    ["layui" => "des", "title" => "腾讯云邮件推送开通地址&nbsp;&nbsp;<a href='https://cloud.tencent.com/act/cps/redirect?redirect=33757&cps_key=b06f66f7257d2a8946c7df2d011c303b' target='_blank'>[点击访问] https://cloud.tencent.com/product/ses</a>", "cname" => "hidden email-tencent"],
                    ["layui" => "input", "title" => "secretId",
                        "name"   => "LC[email][tencent][secretId]",
                        "value"  => $PLG['tencent']['secretId'],
                        "cname"  => "hidden email-tencent"],
                    ["layui" => "input", "title" => "secretkey",
                        "name"   => "LC[email][tencent][secretkey]",
                        "value"  => $PLG['tencent']['secretkey'],
                        "cname"  => "hidden email-tencent"],
                    ["layui" => "input", "title" => "发件人",
                        "name"   => "LC[email][tencent][Alias]",
                        "value"  => $PLG['tencent']['Alias'],
                        "cname"  => "hidden email-tencent"],
                    ["layui" => "input", "title" => "发件地址",
                        "name"   => "LC[email][tencent][From]",
                        "value"  => $PLG['tencent']['From'],
                        "cname"  => "hidden email-tencent"],
                    ["layui" => "input", "title" => "回信地址",
                        "name"   => "LC[email][tencent][Reply]",
                        "value"  => $PLG['tencent']['Reply'],
                        "cname"  => "hidden email-tencent"],
                ];
                $PLG = $plugin['sms'] ?: [];
                $sms = [
                    ["layui" => "title", "title" => "短信配置"],
                    ["layui" => "radio", "title" => "短信接口",
                        "name"   => "LC[sms][type]",
                        "value"  => $PLG['type'] ?: "aliyun",
                        "radio"  => [
                            ["title" => "阿里云", "value" => "aliyun", "tab" => "sms-aliyun"],
                            ["title" => "腾讯云", "value" => "tencent", "tab" => "sms-tencent"],
                        ]],
                    ["layui" => "des", "title" => "阿里云短信开通地址&nbsp;&nbsp;<a href='https://www.aliyun.com/product/sms?userCode=kabw9nx2&tag=share_component&share_source=copy_link' target='_blank'>[点击访问] https://www.aliyun.com/product/sms</a>", "cname" => "hidden sms-aliyun"],
                    ["layui" => "input", "title" => "AccessKeyId",
                        "name"   => "LC[sms][aliyun][AccessKeyId]",
                        "value"  => $PLG['aliyun']['AccessKeyId'],
                        "cname"  => "hidden sms-aliyun"],
                    ["layui" => "input",
                        "title"  => "AccessKeySecret",
                        "name"   => "LC[sms][aliyun][AccessKeySecret]",
                        "value"  => $PLG['aliyun']['AccessKeySecret'],
                        "cname"  => "hidden sms-aliyun"],
                    ["layui" => "des", "title" => "腾讯云短信开通地址&nbsp;&nbsp;<a href='https://cloud.tencent.com/act/cps/redirect?redirect=10068&cps_key=b06f66f7257d2a8946c7df2d011c303b' target='_blank'>[点击访问] https://cloud.tencent.com/product/sms</a>", "cname" => "hidden sms-tencent"],
                    ["layui" => "input", "title" => "secretId",
                        "name"   => "LC[sms][tencent][secretId]",
                        "value"  => $PLG['tencent']['secretId'],
                        "cname"  => "hidden sms-tencent"],
                    ["layui" => "input", "title" => "secretkey",
                        "name"   => "LC[sms][tencent][secretkey]",
                        "value"  => $PLG['tencent']['secretkey'],
                        "cname"  => "hidden sms-tencent"],
                    ["layui" => "input", "title" => "AppId",
                        "name"   => "LC[sms][tencent][SmsSdkAppId]",
                        "value"  => $PLG['tencent']['SmsSdkAppId'],
                        "cname"  => "hidden sms-tencent"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/web_plugin");
                break;
        }
    }
    public function dooss()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                ajaxout(1, "保存成功");
                break;
            default:
                $plugin = LCMS::config([
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                $phpext  = extension_loaded("fileinfo") ? false : true;
                $local   = false;
                $osstype = [
                    ["title"   => "七牛云存储",
                        "value"    => "qiniu",
                        "tab"      => "oss-qiniu",
                        "disabled" => $phpext],
                    ["title"   => "腾讯云存储",
                        "value"    => "tencent",
                        "tab"      => "oss-tencent",
                        "disabled" => $phpext],
                    ["title"   => "阿里云存储",
                        "value"    => "aliyun",
                        "tab"      => "oss-aliyun",
                        "disabled" => $phpext],
                ];
                if (!LCMS::SUPER()) {
                    $aplugin = LCMS::config([
                        "type" => "sys",
                        "cate" => "plugin",
                        "lcms" => true,
                    ]);
                    $local = $aplugin['oss']['must'] > 0 ? true : false;
                }
                $osstype = $local ? $osstype : array_merge([
                    ["title"   => "本地存储",
                        "value"    => "local",
                        "tab"      => "oss-0",
                        "disabled" => $local]], $osstype);
                $form = [
                    ["layui" => "des", "title" => "使用云存储必须开启当前PHP的 fileinfo 扩展！同时注意用户图片、文件上传权限设置！"],
                    ["layui" => "radio", "title" => "存储方式",
                        "name"   => "LC[oss][type]",
                        "value"  => $plugin['oss']['type'] ?: "local",
                        "radio"  => $osstype],
                ];
                if (LCMS::SUPER()) {
                    $form = array_merge($form, [
                        ["layui" => "radio", "title" => "本地权限",
                            "name"   => "LC[oss][must]",
                            "value"  => $plugin['oss']['must'] ?? 0,
                            "radio"  => [
                                ["title" => "子账号可使用本地存储", "value" => 0],
                                ["title" => "子账号必须配置云存储", "value" => 1],
                            ]],
                        ["layui" => "radio", "title" => "云存储权限",
                            "name"   => "LC[oss][super]",
                            "value"  => $plugin['oss']['super'] ?? 0,
                            "cname"  => "hidden oss-qiniu oss-tencent oss-aliyun",
                            "radio"  => [
                                ["title" => "子帐号独立设置", "value" => 0],
                                ["title" => "子账号使用此设置", "value" => 1],
                            ]],
                    ]);
                }
                $form = array_merge($form, [
                    ["layui" => "des", "title" => "特别注意：如果本地有上传过的图片，开启云存储后，需要将本地 <code>upload/</code> 目录下的相关文件先手动上传到云存储！",
                        "cname"  => "hidden oss-qiniu oss-tencent oss-aliyun"],
                    ["layui"      => "input", "title" => "CDN域名",
                        "name"        => "LC[oss][domain]",
                        "value"       => $plugin['oss']['domain'],
                        "placeholder" => "https://www.domain.com/",
                        "cname"       => "hidden oss-qiniu oss-tencent oss-aliyun"],
                    ["layui" => "input", "title" => "AccessKey",
                        "name"   => "LC[oss][qiniu][AccessKey]",
                        "value"  => $plugin['oss']['qiniu']['AccessKey'],
                        "cname"  => "hidden oss-qiniu"],
                    ["layui" => "input", "title" => "SecretKey",
                        "name"   => "LC[oss][qiniu][secretKey]",
                        "value"  => $plugin['oss']['qiniu']['secretKey'],
                        "cname"  => "hidden oss-qiniu"],
                    ["layui" => "input", "title" => "Bucket",
                        "name"   => "LC[oss][qiniu][bucket]",
                        "value"  => $plugin['oss']['qiniu']['bucket'],
                        "cname"  => "hidden oss-qiniu"],
                    ["layui" => "radio", "title" => "存储区域",
                        "name"   => "LC[oss][qiniu][uphost]",
                        "value"  => $plugin['oss']['qiniu']['uphost'],
                        "verify" => "required",
                        "radio"  => [
                            ["title" => "华东", "value" => "hd"],
                            ["title" => "华北", "value" => "hb"],
                            ["title" => "华南", "value" => "hn"],
                        ],
                        "cname"  => "hidden oss-qiniu"],
                    ["layui" => "input", "title" => "SecretId",
                        "name"   => "LC[oss][tencent][SecretId]",
                        "value"  => $plugin['oss']['tencent']['SecretId'],
                        "cname"  => "hidden oss-tencent"],
                    ["layui" => "input", "title" => "SecretKey",
                        "name"   => "LC[oss][tencent][SecretKey]",
                        "value"  => $plugin['oss']['tencent']['SecretKey'],
                        "cname"  => "hidden oss-tencent"],
                    ["layui" => "input", "title" => "请求域名",
                        "name"   => "LC[oss][tencent][Region]",
                        "value"  => $plugin['oss']['tencent']['Region'],
                        "cname"  => "hidden oss-tencent"],
                    ["layui" => "input", "title" => "Bucket",
                        "name"   => "LC[oss][tencent][Bucket]",
                        "value"  => $plugin['oss']['tencent']['Bucket'],
                        "cname"  => "hidden oss-tencent"],
                    ["layui" => "input", "title" => "AccessKeyId",
                        "name"   => "LC[oss][aliyun][AccessKeyId]",
                        "value"  => $plugin['oss']['aliyun']['AccessKeyId'],
                        "cname"  => "hidden oss-aliyun"],
                    ["layui" => "input", "title" => "AccessKeySecret",
                        "name"   => "LC[oss][aliyun][AccessKeySecret]",
                        "value"  => $plugin['oss']['aliyun']['AccessKeySecret'],
                        "cname"  => "hidden oss-aliyun"],
                    ["layui" => "input", "title" => "Endpoint",
                        "name"   => "LC[oss][aliyun][Region]",
                        "value"  => $plugin['oss']['aliyun']['Region'],
                        "cname"  => "hidden oss-aliyun"],
                    ["layui" => "input", "title" => "Bucket",
                        "name"   => "LC[oss][aliyun][Bucket]",
                        "value"  => $plugin['oss']['aliyun']['Bucket'],
                        "cname"  => "hidden oss-aliyun"],
                    ["layui" => "btn", "title" => "立即保存"],
                ]);
                require LCMS::template("own/web_oss");
                break;
        }
    }
    public function dopayment()
    {
        global $_L;
        load::sys_class('table');
        switch ($_L['form']['action']) {
            case 'payment-list':
                TABLE::out(TABLE::set("payment", "lcms = '{$_L['ROOTID']}'", "id DESC"));
                break;
            case 'payment-edit':
                load::sys_class('pays');
                $payment = LCMS::form([
                    "table" => "payment",
                    "do"    => "get",
                    "id"    => $_L['form']['id'],
                ]);
                foreach (PAYS::payment_config() as $key => $val) {
                    $val = json_decode($val, true);

                    $payment_list['payment'][] = [
                        "title" => $val['info']['title'],
                        "value" => $val['info']['name'],
                        "tab"   => "payment_{$val['info']['name']}",
                    ];
                    foreach ($val['form'] as $val2) {
                        if (!empty($payment[$key][$val2['value']])) {
                            $val2['value'] = $payment[$key][$val2['value']];
                        } else {
                            $val2['value'] = $val2['layui'] === "radio" ? "0" : "";
                        }
                        $payment_list['form'][$key][] = $val2;
                    }
                }
                $form['base'][] = [
                    "layui"       => "input",
                    "title"       => "支付名称",
                    "name"        => "LC[title]",
                    "value"       => $payment['title'],
                    "tips"        => "支付名称，仅作分辨使用",
                    "placeholder" => "支付名称，仅作分辨使用",
                    "verify"      => "required",
                ];
                $form['base'][] = [
                    "layui"  => "radio",
                    "title"  => "支付方式",
                    "name"   => "LC[payment]",
                    "value"  => $payment['payment'],
                    "verify" => "required",
                    "radio"  => $payment_list['payment'],
                ];
                $form = array_merge($form, $payment_list['form'], [
                    "last" => [[
                        "layui" => "btn",
                        "title" => "立即保存",
                        "fixed" => true,
                    ]],
                ]);
                require LCMS::template("own/payment-edit");
                break;
            case 'payment-save':
                $_L['form']['LC']['lcms'] = $_L['ROOTID'];
                LCMS::form([
                    "table" => "payment",
                    "key"   => "parameter",
                ]);
                if (sql_error()) {
                    ajaxout(0, "保存失败：" . sql_error());
                } else {
                    ajaxout(1, "保存成功", "close");
                };
                break;
            case 'payment-list-del':
                if (TABLE::del("payment")) {
                    ajaxout(1, "删除成功", "reload");
                } else {
                    ajaxout(0, "删除失败");
                }
                break;
            default:
                $table = [
                    "url"     => "payment&action=payment-list",
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 50],
                        ["title" => "ID", "field" => "id",
                            "width"  => 80,
                            "align"  => "center"],
                        ["title" => "支付名称", "field" => "title",
                            "width"  => 200],
                        ["title" => "支付方式", "field" => "payment",
                            "width"  => 200],
                        ["title"   => "操作", "field" => "do",
                            "minWidth" => 90,
                            "fixed"    => "right",
                            "toolbar"  => [
                                ["title" => "编辑",
                                    "event"  => "iframe",
                                    "url"    => "payment&action=payment-edit",
                                    "color"  => "default"],
                                ["title" => "删除",
                                    "event"  => "ajax",
                                    "url"    => "payment&action=payment-list-del",
                                    "color"  => "danger",
                                    "tips"   => "确认删除？"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => "添加支付", "event" => "iframe",
                            "url"    => "payment&action=payment-edit",
                            "color"  => "default"],
                        ["title" => "批量删除", "event" => "ajax",
                            "url"    => "payment&action=payment-list-del",
                            "color"  => "danger",
                            "tips"   => "确认删除？"],
                    ],
                ];
                require LCMS::template("own/payment-list");
                break;
        }
    }
};
