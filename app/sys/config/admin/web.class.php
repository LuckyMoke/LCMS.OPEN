<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2025-04-15 13:09:00
 * @Description: 基本设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('adminbase');
class web extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doplugin()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                ajaxout(1, "保存成功");
                break;
            case 'test_eamil':
                $email = $LF['email'];
                if (is_email($email)) {
                    LOAD::sys_class("email");
                    $result = EMAIL::send([
                        "TO"    => $email,
                        "Title" => "邮件发送测试",
                        "Body"  => "恭喜您，邮件服务配置成功！",
                    ]);
                }
                if ($result['code'] == 1) {
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
                    ["layui" => "input", "title" => "密码/授权码",
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
                    ["layui" => "input", "title" => "SDK AppId",
                        "name"   => "LC[sms][tencent][SmsSdkAppId]",
                        "value"  => $PLG['tencent']['SmsSdkAppId'],
                        "cname"  => "hidden sms-tencent",
                        "tips"   => "在短信功能的应用列表里获取"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/web_plugin");
                break;
        }
    }
    public function dooss()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                $LC['oss']['domain'] = trim($LC['oss']['domain'], "/");
                if (!in_string($LC['oss']['domain'], ["http://", "https://"])) {
                    $LC['oss']['domain'] = "http://{$LC['oss']['domain']}";
                }
                $LC['oss']['domain'] .= "/";
                $LC['oss']['domain'] = realhost($LC['oss']['domain']);
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
                    ["title"   => "七牛云",
                        "value"    => "qiniu",
                        "tab"      => "oss-qiniu",
                        "disabled" => $phpext],
                    ["title"   => "腾讯云",
                        "value"    => "tencent",
                        "tab"      => "oss-tencent",
                        "disabled" => $phpext],
                    ["title"   => "阿里云",
                        "value"    => "aliyun",
                        "tab"      => "oss-aliyun",
                        "disabled" => $phpext],
                    ["title"   => "百度云",
                        "value"    => "baidu",
                        "tab"      => "oss-baidu",
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
                    ["title"   => "本地",
                        "value"    => "local",
                        "tab"      => "oss-0",
                        "disabled" => $local]], $osstype);
                $form = [
                    ["layui" => "des", "title" => "使用云存储必须开启当前PHP的 fileinfo 扩展！同时注意用户图片、文件上传权限设置！"],
                    ["layui" => "title", "title" => "存储设置"],
                    ["layui" => "radio", "title" => "存储方式",
                        "name"   => "LC[oss][type]",
                        "value"  => $plugin['oss']['type'] ?: "local",
                        "radio"  => $osstype],
                    ["layui" => "radio", "title" => "图片裁剪",
                        "name"   => "LC[thumb][type]",
                        "value"  => $plugin['thumb']['type'] ?: 0,
                        "radio"  => [
                            ["title" => "铺满图片", "value" => 0],
                            ["title" => "背景留白", "value" => 1],
                        ]],
                ];
                if (LCMS::SUPER()) {
                    $form = array_merge($form, [
                        ["layui" => "radio", "title" => "本地权限",
                            "name"   => "LC[oss][must]",
                            "value"  => $plugin['oss']['must'] ?? 0,
                            "radio"  => [
                                ["title" => "用户账号可使用本地存储", "value" => 0],
                                ["title" => "用户账号必须配置云存储", "value" => 1],
                            ]],
                        ["layui" => "radio", "title" => "云存储权限",
                            "name"   => "LC[oss][super]",
                            "value"  => $plugin['oss']['super'] ?? 0,
                            "cname"  => "hidden oss-qiniu oss-tencent oss-aliyun oss-baidu",
                            "radio"  => [
                                ["title" => "用户账号独立设置", "value" => 0],
                                ["title" => "用户账号使用此设置", "value" => 1],
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
                        "tips"        => "请填写完整域名地址 https://www.domain.com/",
                        "cname"       => "hidden oss-qiniu oss-tencent oss-aliyun oss-baidu"],
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
                            ["title" => "华东-浙江", "value" => "z0"],
                            ["title" => "华东-浙江2", "value" => "cn-east-2"],
                            ["title" => "华北-河北", "value" => "z1"],
                            ["title" => "华南-广东", "value" => "z2"],
                            ["title" => "北美-洛杉矶", "value" => "na0"],
                            ["title" => "亚太-新加坡", "value" => "as0"],
                            ["title" => "亚太-河内", "value" => "ap-southeast-2"],
                            ["title" => "亚太-胡志明", "value" => "ap-southeast-3"],
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
                    ["layui" => "input", "title" => "Access Key",
                        "name"   => "LC[oss][baidu][AccessKey]",
                        "value"  => $plugin['oss']['baidu']['AccessKey'],
                        "cname"  => "hidden oss-baidu"],
                    ["layui" => "input", "title" => "Secret Key",
                        "name"   => "LC[oss][baidu][SecretKey]",
                        "value"  => $plugin['oss']['baidu']['SecretKey'],
                        "cname"  => "hidden oss-baidu"],
                    ["layui" => "input", "title" => "官方域名",
                        "name"   => "LC[oss][baidu][Region]",
                        "value"  => $plugin['oss']['baidu']['Region'],
                        "cname"  => "hidden oss-baidu"],
                    ["layui" => "input", "title" => "Bucket",
                        "name"   => "LC[oss][baidu][Bucket]",
                        "value"  => $plugin['oss']['baidu']['Bucket'],
                        "cname"  => "hidden oss-baidu"],
                ]);
                $form = array_merge($form, [
                    ["layui" => "title", "title" => "图片水印"],
                    ["layui" => "des", "title" => "小提示：如果哪张图片不需要加水印，可将此图片转换为<code>gif</code>格式再上传！"],
                    ["layui" => "radio", "title" => "水印功能",
                        "name"   => "LC[watermark][on]",
                        "value"  => $plugin['watermark']['on'] ?: 0,
                        "radio"  => [
                            ["title" => "开启", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ]],
                    ["layui" => "input", "title" => "水印文字",
                        "name"   => "LC[watermark][text]",
                        "value"  => $plugin['watermark']['text'] ?: "我是水印",
                        "verify" => "required"],
                    ["layui"  => "slider", "title" => "文字大小",
                        "name"    => "LC[watermark][size]",
                        "value"   => $plugin['watermark']['size'] ?: 18,
                        "min"     => 14,
                        "max"     => 60,
                        "step"    => 2,
                        "settips" => "PX",
                        "verify"  => "required"],
                    ["layui" => "color", "title" => "水印颜色",
                        "name"   => "LC[watermark][fill]",
                        "value"  => $plugin['watermark']['fill'] ?: "#FFFFFF",
                        "format" => "hex",
                        "verify" => "required"],
                    ["layui"  => "slider", "title" => "透明度",
                        "name"    => "LC[watermark][dissolve]",
                        "value"   => $plugin['watermark']['dissolve'] ?: 100,
                        "min"     => 10,
                        "max"     => 100,
                        "step"    => 10,
                        "settips" => "%"],
                    ["layui"  => "slider", "title" => "文字阴影",
                        "name"    => "LC[watermark][shadow]",
                        "value"   => $plugin['watermark']['shadow'] ?: 50,
                        "min"     => 0,
                        "max"     => 100,
                        "step"    => 10,
                        "settips" => "%"],
                    ["layui" => "radio", "title" => "水印位置",
                        "name"   => "LC[watermark][gravity]",
                        "value"  => $plugin['watermark']['gravity'],
                        "radio"  => [
                            ["title" => "左上", "value" => "NorthWest"],
                            ["title" => "中上", "value" => "North"],
                            ["title" => "右上", "value" => "NorthEast"],
                        ]],
                    ["layui" => "radio", "title" => "水印位置",
                        "name"   => "LC[watermark][gravity]",
                        "value"  => $plugin['watermark']['gravity'] ?: "Center",
                        "radio"  => [
                            ["title" => "左中", "value" => "West"],
                            ["title" => "居中", "value" => "Center"],
                            ["title" => "右中", "value" => "East"],
                        ]],
                    ["layui" => "radio", "title" => "水印位置",
                        "name"   => "LC[watermark][gravity]",
                        "value"  => $plugin['watermark']['gravity'],
                        "radio"  => [
                            ["title" => "左下", "value" => "SouthWest"],
                            ["title" => "中下", "value" => "South"],
                            ["title" => "右下", "value" => "SouthEast"],
                        ]],
                    ["layui" => "input_sort", "title" => "横向边距",
                        "name"   => "LC[watermark][dx]",
                        "value"  => $plugin['watermark']['dx'] ?: 0,
                        "type"   => "number",
                        "tips"   => "PX",
                        "verify" => "required"],
                    ["layui" => "input_sort", "title" => "纵向边距",
                        "name"   => "LC[watermark][dy]",
                        "value"  => $plugin['watermark']['dy'] ?: 0,
                        "type"   => "number",
                        "tips"   => "PX",
                        "verify" => "required"],
                    ["layui" => "btn", "title" => "立即保存"],
                ]);
                require LCMS::template("own/web_oss");
                break;
        }
    }
    public function doaimodel()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "plugin",
                    "form" => [
                        "aimodel" => $LC,
                    ],
                ]);
                ajaxout(1, "保存成功", "reload-top");
                break;
            default:
                $plugin = LCMS::config([
                    "type" => "sys",
                    "cate" => "plugin",
                ]);
                $PLG    = $plugin['aimodel'] ?: [];
                $models = json_decode(file_get_contents(PATH_APP_NOW . "include/resource/models.json"), true);
                $form   = [
                    ["layui" => "des", "title" => "大模型官网：<a href='https://cloud.baidu.com/product/wenxinworkshop' target='_blank'>百度</a>、<a href='https://www.aliyun.com/product/bailian' target='_blank'>阿里</a>、<a href='https://www.volcengine.com/product/ark' target='_blank'>火山</a>、<a href='https://cloud.siliconflow.cn?referrer=clyb84b9p00eb14lc4hjghfus' target='_blank'>硅基流动</a>、<a href='https://platform.deepseek.com/' target='_blank'>DeepSeek</a>、<a href='https://api2d.com/r/189177' target='_blank'>ChatGPT-API2D</a>、<a href='https://openai.com/' target='_blank'>ChatGPT-官方</a><br>▲ 本服务API由第三方提供，API请求均在你本地电脑执行，请确保你本地电脑可以访问对应服务<br>▲ 有<code>多家永久免费接口</code>可使用，具体请查看AI模型中，标注为“免费”的模型"],
                    ["layui" => "radio", "title" => "API大模型",
                        "name"   => "LC[type]",
                        "value"  => $PLG['type'] ?: "",
                        "radio"  => [
                            ["title" => "关闭", "value" => "", "tab" => "type_close"],
                            ["title" => "百度", "value" => "wenxin", "tab" => "type_wenxin"],
                            ["title" => "阿里", "value" => "aliyun", "tab" => "type_aliyun"],
                            ["title" => "火山", "value" => "huoshan", "tab" => "type_huoshan"],
                            ["title" => "硅基流动", "value" => "siliconcloud", "tab" => "type_siliconcloud"],
                            ["title" => "DeepSeek", "value" => "deepseek", "tab" => "type_deepseek"],
                            ["title" => "ChatGPT", "value" => "openai", "tab" => "type_openai"],
                        ]],
                ];
                if (!LCMS::SUPER()) {
                    $form = array_merge($form, [
                        ["layui" => "radio", "title" => "子用户AI",
                            "name"   => "LC[subon]",
                            "value"  => $PLG['subon'] ?? 1,
                            "radio"  => [
                                ["title" => "启用", "value" => 1],
                                ["title" => "禁用", "value" => 0],
                            ]],
                    ]);
                }
                $form = array_merge($form, [
                    ["layui" => "input_sort", "title" => "最大TOKENS",
                        "name"   => "LC[max_tokens]",
                        "value"  => $PLG['max_tokens'] ?: 1024,
                        "type"   => "number",
                        "min"    => 100,
                        "max"    => 2048,
                        "tips"   => "单次请求最大输出tokens数"],
                    ["layui" => "input", "title" => "Access Key",
                        "name"   => "LC[wenxin][access_key]",
                        "value"  => $PLG['wenxin']['access_key'],
                        "cname"  => "hidden type_wenxin"],
                    ["layui" => "input", "title" => "Secret Key",
                        "name"   => "LC[wenxin][secret_key]",
                        "value"  => $PLG['wenxin']['secret_key'],
                        "cname"  => "hidden type_wenxin"],
                    ["layui" => "select", "title" => "AI模型",
                        "name"   => "LC[wenxin][model]",
                        "value"  => $PLG['wenxin']['model'],
                        "cname"  => "hidden type_wenxin",
                        "option" => $models['wenxin']],
                    ["layui" => "input", "title" => "API-KEY",
                        "name"   => "LC[aliyun][token]",
                        "value"  => $PLG['aliyun']['token'],
                        "cname"  => "hidden type_aliyun"],
                    ["layui" => "select", "title" => "AI模型",
                        "name"   => "LC[aliyun][model]",
                        "value"  => $PLG['aliyun']['model'],
                        "cname"  => "hidden type_aliyun",
                        "option" => $models['aliyun']],
                    ["layui" => "input", "title" => "API Key",
                        "name"   => "LC[huoshan][token]",
                        "value"  => $PLG['huoshan']['token'],
                        "cname"  => "hidden type_huoshan"],
                    ["layui" => "input", "title" => "推理节点",
                        "name"   => "LC[huoshan][model]",
                        "value"  => $PLG['huoshan']['model'],
                        "cname"  => "hidden type_huoshan"],
                    ["layui" => "input", "title" => "API密钥",
                        "name"   => "LC[siliconcloud][token]",
                        "value"  => $PLG['siliconcloud']['token'],
                        "cname"  => "hidden type_siliconcloud"],
                    ["layui" => "select", "title" => "AI模型",
                        "name"   => "LC[siliconcloud][model]",
                        "value"  => $PLG['siliconcloud']['model'],
                        "cname"  => "hidden type_siliconcloud",
                        "option" => $models['siliconcloud']],
                    ["layui" => "input", "title" => "API key",
                        "name"   => "LC[deepseek][token]",
                        "value"  => $PLG['deepseek']['token'],
                        "cname"  => "hidden type_deepseek"],
                    ["layui" => "select", "title" => "AI模型",
                        "name"   => "LC[deepseek][model]",
                        "value"  => $PLG['deepseek']['model'],
                        "cname"  => "hidden type_deepseek",
                        "option" => $models['deepseek']],
                    ["layui" => "radio", "title" => "接口提供商",
                        "name"   => "LC[openai][type]",
                        "value"  => $PLG['openai']['type'] ?: "api2d",
                        "radio"  => [
                            ["title" => "API2D/境内可用", "value" => "api2d"],
                            ["title" => "OpenAI/官方原接口", "value" => "openai"],
                        ],
                        "cname"  => "hidden type_openai"],
                    ["layui"      => "input", "title" => "自定义接口",
                        "name"        => "LC[openai][api]",
                        "value"       => $PLG['openai']['api'],
                        "placeholder" => "不填使用默认接口地址",
                        "cname"       => "hidden type_openai"],
                    ["layui" => "input", "title" => "TOKEN",
                        "name"   => "LC[openai][token]",
                        "value"  => $PLG['openai']['token'],
                        "cname"  => "hidden type_openai"],
                    ["layui" => "select", "title" => "AI模型",
                        "name"   => "LC[openai][model]",
                        "value"  => $PLG['openai']['model'],
                        "cname"  => "hidden type_openai",
                        "option" => $models['openai']],
                    ["layui" => "btn", "title" => "立即保存"],
                ]);
                require LCMS::template("own/web_aimodel");
                break;
        }
    }
    public function dopayment()
    {
        global $_L, $LF, $LC;
        LOAD::sys_class('table');
        LOAD::sys_class('pays');
        switch ($LF['action']) {
            case 'payment-list':
                TABLE::out(TABLE::set("payment", "lcms = '{$_L['ROOTID']}'", "id DESC"));
                break;
            case 'payment-edit':
                $payment = LCMS::form([
                    "table" => "payment",
                    "do"    => "get",
                    "id"    => $LF['id'],
                ]);
                foreach (PAYS::payment_config() as $key => $val) {
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
                $LC['lcms'] = $_L['ROOTID'];
                LCMS::form([
                    "table" => "payment",
                    "key"   => "parameter",
                    "form"  => $LC,
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
            case 'payment-agent':
                ajaxout(1, "success", "", PAYS::payment_list("{$LF['payment']}_agent"));
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
