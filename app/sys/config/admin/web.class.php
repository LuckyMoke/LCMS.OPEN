<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2020-10-28 13:49:44
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
                $email = $_L['form']['LC']['email'];
                if ($email) {
                    load::sys_class("email");
                    $r = EMAIL::send([
                        "to"       => $email['from'],
                        "toname"   => "发送测试",
                        "subject"  => "邮件发送测试",
                        "body"     => "恭喜您！邮件服务配置成功！",
                        "fromname" => $email['fromname'],
                        "from"     => $email['from'],
                        "pass"     => $email['pass'],
                        "smtp"     => $email['smtp'],
                        "ssl"      => $email['ssl'],
                        "port"     => $email['port'],
                    ]);
                }
                if ($r['code'] == "1") {
                    ajaxout(1, "邮件服务配置成功");
                } else {
                    ajaxout(0, "邮件服务配置失败！{$r['msg']}");
                }
                break;
            default:
                $plugin = LCMS::config([
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                $email = [
                    ["layui" => "title", "title" => "邮箱配置"],
                    ["layui" => "input", "title" => "发件人",
                        "name"   => "LC[email][fromname]",
                        "value"  => $plugin['email']['fromname']],
                    ["layui" => "input", "title" => "邮箱账号",
                        "name"   => "LC[email][from]",
                        "value"  => $plugin['email']['from']],
                    ["layui" => "input", "title" => "SMTP密码",
                        "name"   => "LC[email][pass]",
                        "value"  => $plugin['email']['pass'],
                        "type"   => "password"],
                    ["layui" => "input", "title" => "SMTP服务器",
                        "name"   => "LC[email][smtp]",
                        "value"  => $plugin['email']['smtp']],
                    ["layui" => "on", "title" => "TLS/SSL",
                        "name"   => "LC[email][ssl]",
                        "value"  => $plugin['email']['ssl'],
                        "text"   => "SSL|TLS"],
                    ["layui" => "input", "title" => "端口",
                        "name"   => "LC[email][port]",
                        "value"  => $plugin['email']['port'],
                        "tips"   => "一般情况下<br>SSL端口为465，TLS端口为25"],
                ];
                $alisms = [
                    ["layui" => "title", "title" => "阿里云短信配置"],
                    ["layui" => "des", "title" => "阿里云短信开通地址&nbsp;&nbsp;<a href='https://www.aliyun.com/product/sms?userCode=kabw9nx2&tag=share_component&share_source=copy_link' target='_blank'>[点击访问] https://www.aliyun.com/product/sms</a>"],
                    ["layui" => "input", "title" => "AccessKey ID",
                        "name"   => "LC[alisms][id]",
                        "value"  => $plugin['alisms']['id']],
                    ["layui" => "input",
                        "title"  => "Access Key Secret",
                        "name"   => "LC[alisms][secret]",
                        "value"  => $plugin['alisms']['secret']],
                    ["layui" => "input", "title" => "短信签名",
                        "name"   => "LC[alisms][sign]",
                        "value"  => $plugin['alisms']['sign']],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/web_plugin");
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
                $payment_config = PAYS::payment_config();
                foreach ($payment_config as $key => $val) {
                    $val                       = json_decode($val, true);
                    $payment_list['payment'][] = [
                        "title" => $val['info']['title'],
                        "value" => $val['info']['name'],
                        "tab"   => "payment_{$val['info']['name']}",
                    ];
                    foreach ($val['form'] as $val2) {
                        if (!empty($payment[$key][$val2['value']])) {
                            $val2['value'] = $payment[$key][$val2['value']];
                        } else {
                            $val2['value'] = $val2['layui'] == "radio" ? "0" : "";
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
                foreach (["wechat" => "mch_id", "wechat_agent" => "sub_mch_id"] as $payment => $mch_id) {
                    if ($_L['form']['LC'][$payment][$mch_id] && $_L['form']['LC'][$payment]['apiclient_cert'] && $_L['form']['LC'][$payment]['apiclient_key']) {
                        $dir  = PATH_CORE_PLUGIN . "payment/{$payment}/cert/";
                        $file = $dir . md5($_L['form']['LC'][$payment][$mch_id]);
                        file_put_contents("{$file}_cert.pem", $_L['form']['LC'][$payment]['apiclient_cert']);
                        file_put_contents("{$file}_key.pem", $_L['form']['LC'][$payment]['apiclient_key']);
                    }
                }
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
                    ajaxout(1, "删除成功");
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
