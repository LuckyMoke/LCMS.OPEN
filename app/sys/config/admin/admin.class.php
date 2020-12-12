<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2020-12-11 16:52:49
 * @Description: 全局设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class admin extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                $_L['form']['LC']['domain'] = str_replace(["http://", "https://", "/"], "", $_L['form']['LC']['domain']);
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
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
                $config['oauth_code'] = $config['oauth_code'] ?: strtoupper(md5(HTTP_HOST)) . randstr(32);
                $form                 = array(
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
                    ["layui" => "upload",
                        "title"  => "后台LOGO",
                        "name"   => "LC[logo]",
                        "value"  => $config['logo'],
                    ],
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
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                $domain = parse_url($_L['form']['LC']['domain']);
                if ($domain['host']) {
                    $_L['form']['LC']['https']  = $domain['scheme'] == "https" ? "1" : "0";
                    $_L['form']['LC']['domain'] = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
                }
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "web",
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
                        "value"  => $config['domain_must'],
                        "tips"   => "限制前端只能通过下方域名访问",
                        "radio"  => [
                            ["title" => "限制域名", "value" => "1"],
                            ["title" => "不限制域名", "value" => "0"],
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
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                if ($_L['form']['LC']['dir'] != $_L['config']['admin']['dir']) {
                    if (!getdirpower(PATH_WEB)) {
                        unset($_L['form']['LC']['dir']);
                        ajaxout(1, "根目录没有写权限", "reload");
                    } else {
                        $change = true;
                    }
                }
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ]);
                if ($change) {
                    ajaxout(1, "保存成功", "{$_L['url']['own_form']}change&olddir={$_L['config']['admin']['dir']}&newdir={$_L['form']['LC']['dir']}");
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
                    ["layui" => "input", "title" => "后台目录",
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
                            ["title" => "Luosimao人机验证", "value" => "luosimao", "tab" => "login_code_luosimao"],
                        ]],
                    ["layui"      => "input", "title" => "使用域名",
                        "name"        => "LC[login_code][domain]",
                        "value"       => $config['login_code']['domain'],
                        "cname"       => "hidden login_code_luosimao login_code_tencent",
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
                    ["layui" => "title", "title" => "上传安全"],
                    ["layui" => "input", "title" => "上传大小",
                        "name"   => "LC[attsize]",
                        "value"  => $config['attsize'],
                        "tips"   => "限制上传文件的大小，单位KB",
                        "verify" => "required"],
                    ["layui" => "tags", "title" => "格式白名单",
                        "name"   => "LC[mimelist]",
                        "value"  => $config['mimelist'],
                        "tips"   => "允许上传白名单里的文件格式",
                        "verify" => "required"],
                    ["layui" => "title", "title" => "性能优化"],
                    ["layui" => "radio", "title" => "开发模式",
                        "name"   => "LC[development]",
                        "value"  => $config['development'] ?: "0",
                        "radio"  => [
                            ["title" => "打开", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ]],
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
        global $_L;
        if (LCMS::SUPER()) {
            if ($_L['form']['olddir'] && $_L['form']['newdir'] && is_dir(PATH_WEB . $_L['form']['olddir']) && !is_dir(PATH_WEB . $_L['form']['newdir']) && movedir(PATH_WEB . $_L['form']['olddir'], PATH_WEB . $_L['form']['newdir'])) {
                echo '<script type="text/javascript">top.location.href = "' . $_L['url']['site'] . $_L['form']['newdir'] . '";</script>';
            } else {
                LCMS::X(500, "发生致命错误，您需要在FTP中手动修改后台目录");
            }
        } else {
            LCMS::X(403, "您没有权限修改后台目录");
        }
    }
    public function doclear()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                if ($_L['form']['tpl']) {
                    deldir(PATH_CACHE . "tpl");
                }
                if ($_L['form']['static']) {
                    deldir(PATH_CACHE . "static");
                }
                if ($_L['form']['cfg']) {
                    deldir(PATH_CACHE . "cfg");
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
        global $_L;
        if ($_L['form']['checked'] == "1") {
            load::plugin("Redis/rds");
            $redis = new RDS();
            $redis->do->setex("LCMSREDISTEST", 60, "success");
            if ($redis->do->get('LCMSREDISTEST') == "success") {
                ajaxout(1);
            } else {
                ajaxout(0, "Redis未成功开启");
            }
        } else {
            ajaxout(1);
        }
    }
}
