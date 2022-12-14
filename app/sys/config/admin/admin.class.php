<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2022-11-13 17:13:20
 * @Description: 全局设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class admin extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
        switch ($LF['action']) {
            case 'save':
                if (in_string($LC['domain'], ["http://", "https://"])) {
                    $LC['domain'] = parse_url($LC['domain'])['host'];
                }
                $LC['oauth_code'] = strtoupper(md5(HTTP_HOST));
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
                    "form" => $LC,
                    "lcms" => true,
                ]);
                ajaxout(1, "保存成功");
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ]);
                $form = array(
                    ["layui" => "title", "title" => "基础信息"],
                    ["layui" => "input", "title" => "系统名称",
                        "name"   => "LC[title]",
                        "value"  => $config['title'],
                        "verify" => "required",
                    ],
                    ["layui" => "radio", "title" => "访问协议",
                        "name"   => "LC[https]",
                        "value"  => $config['https'] ? $config['https'] : "0",
                        "radio"  => [
                            ["title" => "https://", "value" => "1"],
                            ["title" => "http://", "value" => "0"],
                        ],
                        "tips"   => "如果使用了cdn半程加密，会用到",
                    ],
                    ["layui"      => "input", "title" => "强制域名",
                        "name"        => "LC[domain]",
                        "value"       => $config['domain'],
                        "placeholder" => "不填任意域名可访问后台",
                        "tips"        => "不填任意域名可打开后台，填写后任意域名访问后台，均自动跳转到填写的域名",
                    ],
                    ["layui" => "input", "title" => "开发者",
                        "name"   => "LC[developer]",
                        "value"  => $config['developer'],
                        "verify" => "required"],
                    ["layui" => "title", "title" => "通知公告"],
                    ["layui" => "editor", "title" => "后台公告",
                        "name"   => "LC[gonggao]",
                        "value"  => $config['gonggao'],
                    ],
                    ["layui" => "btn", "title" => "立即保存"],
                );
                require LCMS::template("own/admin_index");
                break;
        }
    }
    public function doweb()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
        switch ($LF['action']) {
            case 'save':
                $domain = parse_url($LC['domain']);
                if ($domain['host']) {
                    $LC['https']  = $domain['scheme'] === "https" ? "1" : "0";
                    $LC['domain'] = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
                }
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "web",
                    "form" => $LC,
                    "lcms" => true,
                ]);
                ajaxout(1, "保存成功");
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "web",
                    "lcms" => true,
                ]);
                $scheme = $config['https'] == "1" ? "https://" : "http://";
                $form   = array(
                    ["layui" => "radio", "title" => "限制访问？",
                        "name"   => "LC[domain_must]",
                        "value"  => $config['domain_must'] ?: 0,
                        "tips"   => "限制前端只能通过下方域名访问，除非你特别懂，否则请不要限制",
                        "radio"  => [
                            ["title" => "限制域名", "value" => 1],
                            ["title" => "不限制域名", "value" => 0],
                        ],
                    ],
                    ["layui"      => "input", "title" => "默认前端域名",
                        "name"        => "LC[domain]",
                        "value"       => $config['domain'] ? "{$scheme}{$config['domain']}/" : "",
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意结尾的 / 斜杠",
                        "verify"      => "required",
                    ],
                    ["layui"      => "input", "title" => "默认API域名",
                        "name"        => "LC[domain_api]",
                        "value"       => $config['domain_api'],
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意结尾的 / 斜杠",
                        "verify"      => "required",
                    ],
                    ["layui" => "input", "title" => "默认前端Title",
                        "name"   => "LC[title]",
                        "value"  => $config['title'],
                    ],
                    ["layui" => "upload", "title" => "默认前端图片",
                        "name"   => "LC[image_default]",
                        "value"  => $config['image_default'],
                    ],
                    ["layui" => "textarea", "title" => "平台统计代码",
                        "name"   => "LC[tongji]",
                        "value"  => $config['tongji'],
                    ],
                    ["layui" => "btn", "title" => "立即保存"],
                );
                require LCMS::template("own/admin_web");
                break;
        }
    }
    public function dosafe()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
        switch ($LF['action']) {
            case 'save':
                if ($LC['dir'] != $_L['config']['admin']['dir']) {
                    if (!getdirpower(PATH_WEB)) {
                        unset($LC['dir']);
                        ajaxout(1, "根目录没有写权限", "reload");
                    } else {
                        $change = true;
                    }
                }
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
                    "form" => $LC,
                    "lcms" => true,
                ]);
                if ($change) {
                    ajaxout(1, "保存成功", "{$_L['url']['own_form']}change&olddir={$_L['config']['admin']['dir']}&newdir={$LC['dir']}");
                } else {
                    ajaxout(1, "保存成功", "reload");
                }
                break;
            default:
                $config = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                $form = [
                    ["layui" => "title", "title" => "登陆安全"],
                    ["layui" => "input_sort", "title" => "后台目录",
                        "name"   => "LC[dir]",
                        "value"  => $config['dir'] ?: "admin",
                        "tips"   => "建议修改后台目录提高安全性",
                        "verify" => "required"],
                    ["layui"  => "slider", "title" => "自动登出",
                        "name"    => "LC[sessiontime]",
                        "value"   => $config['sessiontime'],
                        "tips"    => "指定时间无操作自动登出，0为不自动登出",
                        "min"     => "0",
                        "max"     => "60",
                        "step"    => "5",
                        "settips" => "分钟"],
                    ["layui" => "radio", "title" => "登陆限制",
                        "name"   => "LC[login_limit]",
                        "value"  => $config['login_limit'] ?? "0",
                        "tips"   => "是否限制一个账号可同时在多个设备登陆",
                        "radio"  => [
                            ["title" => "单设备", "value" => "0"],
                            ["title" => "多设备", "value" => "1"],
                        ]],
                    ["layui" => "radio", "title" => "登陆验证码",
                        "name"   => "LC[login_code][type]",
                        "value"  => $config['login_code']['type'] ?? "0",
                        "radio"  => [
                            ["title" => "图片验证码", "value" => "0", "tab" => "login_code"],
                            ["title" => "Luosimao", "value" => "luosimao", "tab" => "login_code_luosimao"],
                            ["title" => "Vaptcha", "value" => "vaptcha", "tab" => "login_code_vaptcha"],
                            ["title" => "极验行为验", "value" => "geetest", "tab" => "login_code_geetest"],
                            ["title" => "reCAPTCHA", "value" => "recaptcha", "tab" => "login_code_recaptcha"],
                        ]],
                    ["layui"      => "input", "title" => "使用域名",
                        "name"        => "LC[login_code][domain]",
                        "value"       => $config['login_code']['domain'],
                        "cname"       => "hidden login_code_luosimao login_code_geetest login_code_vaptcha login_code_recaptcha",
                        "placeholder" => "请填写主域名！",
                        "tips"        => "请填写主域名！"],
                    ["layui" => "input", "title" => "site_key",
                        "name"   => "LC[login_code][luosimao][site_key]",
                        "value"  => $config['login_code']['luosimao']['site_key'],
                        "cname"  => "hidden login_code_luosimao"],
                    ["layui" => "input", "title" => "api_key",
                        "name"   => "LC[login_code][luosimao][api_key]",
                        "value"  => $config['login_code']['luosimao']['api_key'],
                        "cname"  => "hidden login_code_luosimao"],
                    ["layui" => "input", "title" => "VID",
                        "name"   => "LC[login_code][vaptcha][vid]",
                        "value"  => $config['login_code']['vaptcha']['vid'],
                        "cname"  => "hidden login_code_vaptcha"],
                    ["layui" => "input", "title" => "KEY",
                        "name"   => "LC[login_code][vaptcha][key]",
                        "value"  => $config['login_code']['vaptcha']['key'],
                        "cname"  => "hidden login_code_vaptcha"],
                    ["layui" => "input", "title" => "验证ID",
                        "name"   => "LC[login_code][geetest][captcha_id]",
                        "value"  => $config['login_code']['geetest']['captcha_id'],
                        "cname"  => "hidden login_code_geetest"],
                    ["layui" => "input", "title" => "验证Key",
                        "name"   => "LC[login_code][geetest][captcha_key]",
                        "value"  => $config['login_code']['geetest']['captcha_key'],
                        "cname"  => "hidden login_code_geetest"],
                    ["layui" => "input", "title" => "网站秘钥",
                        "name"   => "LC[login_code][recaptcha][site_key]",
                        "value"  => $config['login_code']['recaptcha']['site_key'],
                        "cname"  => "hidden login_code_recaptcha"],
                    ["layui" => "input", "title" => "通信秘钥",
                        "name"   => "LC[login_code][recaptcha][secret]",
                        "value"  => $config['login_code']['recaptcha']['secret'],
                        "cname"  => "hidden login_code_recaptcha"],
                    ["layui" => "title", "title" => "后台水印"],
                    ["layui" => "radio", "title" => "功能开关",
                        "name"   => "LC[admin_water]",
                        "value"  => $config['admin_water'] ?: 0,
                        "radio"  => [
                            ["title" => "开启", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ],
                        "tips"   => "后台界面显示用户名等水印"],
                    ["layui" => "title", "title" => "上传安全"],
                    ["layui" => "input_sort", "title" => "上传大小",
                        "name"   => "LC[attsize]",
                        "value"  => $config['attsize'],
                        "tips"   => "限制上传文件的大小，单位KB",
                        "verify" => "required"],
                    ["layui" => "radio", "title" => "图片转WEBP",
                        "name"   => "LC[attwebp]",
                        "value"  => $config['attwebp'] ?: 0,
                        "radio"  => [
                            ["title" => "打开", "value" => 1, "tab" => "attwebp1"],
                            ["title" => "关闭", "value" => 0, "tab" => "attwebp0"],
                        ],
                        "tips"   => "不知道这是什么不要开，如果开启后无法上传图片，请关掉"],
                    ["layui"  => "slider", "title" => "图片压缩率",
                        "name"    => "LC[attquality]",
                        "value"   => $config['attquality'] ?: 70,
                        "tips"    => "jpg格式图片压缩率，100%为不压缩",
                        "min"     => 10,
                        "max"     => 100,
                        "step"    => 10,
                        "settips" => "%",
                        "cname"   => "hidden attwebp0"],
                    ["layui" => "des", "title" => "特别注意：为了后台安全，一些不常见的文件后缀，请在上传完文件后，及时删除白名单。"],
                    ["layui" => "tags", "title" => "格式白名单",
                        "name"   => "LC[mimelist]",
                        "value"  => $_L['config']['admin']['mimelist'],
                        "tips"   => "上传格式白名单，例如jpg，不需要加.点",
                        "verify" => "required"],
                    ["layui" => "title", "title" => "性能优化"],
                    ["layui" => "radio", "title" => "开发模式",
                        "name"   => "LC[development]",
                        "value"  => $config['development'] ?: "0",
                        "radio"  => [
                            ["title" => "打开", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ],
                        "tips"   => "使用完请及时关闭，只建议本地开发时开启"],
                    ["layui" => "on", "title" => "SESSION",
                        "name"   => "LC[session_type]",
                        "value"  => $config['session_type'] ?: "0",
                        "text"   => "Redis存储|文件存储",
                        "tips"   => "SESSION的存储方式",
                        "url"    => "checkredis"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/admin_safe");
                break;
        }
    }
    public function dochange()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() || LCMS::X(403, "此功能仅超级管理员可用");
        if ($LF['olddir'] && $LF['newdir'] && is_dir(PATH_WEB . $LF['olddir']) && !is_dir(PATH_WEB . $LF['newdir']) && movedir(PATH_WEB . $LF['olddir'], PATH_WEB . $LF['newdir'])) {
            echo '<script type="text/javascript">top.location.href = "' . $_L['url']['site'] . $LF['newdir'] . '";</script>';
        } else {
            LCMS::X(500, "发生致命错误，您需要在FTP中手动修改后台目录");
        }
    }
    public function doclear()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                if ($LF['tpl']) {
                    deldir(PATH_CACHE . "tpl");
                }
                if ($LF['static']) {
                    deldir(PATH_CACHE . "static");
                }
                if ($LF['cfg']) {
                    sql_delete(["cache"]);
                }
                ajaxout(1, "清除成功", "close");
                break;
            default:
                $form = [
                    ["layui"   => "checkbox", "title" => "缓存类型",
                        "checkbox" => [
                            ["title" => "页面缓存", "name" => "tpl", "value" => "1"],
                            ["title" => "CSS/JS缓存", "name" => "static", "value" => "0"],
                            ["title" => "临时配置", "name" => "cfg", "value" => "0"],
                        ],
                    ],
                    ["layui" => "btn", "title" => "立即清除"],
                ];
                require LCMS::template("own/clear");
                break;
        }
    }
    public function docheckredis()
    {
        global $_L, $LF, $LC;
        if ($LF['checked'] == "1") {
            load::plugin("Redis/rds");
            $redis = new RDS();
            $redis->do->setex("LCMSREDISTEST", 60, "success");
            if ($redis->do->get('LCMSREDISTEST') === "success") {
                ajaxout(1);
            } else {
                ajaxout(0, "Redis未成功开启");
            }
        } else {
            ajaxout(1);
        }
    }
}
