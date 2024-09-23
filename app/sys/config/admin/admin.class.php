<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2024-09-21 22:20:36
 * @Description: 全局设置
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('adminbase');
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
                $LC['domain'] = realhost($LC['domain']);
                LCMS::config([
                    "do"    => "save",
                    "type"  => "sys",
                    "cate"  => "admin",
                    "unset" => "app_host",
                    "form"  => $LC,
                    "lcms"  => true,
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
                    ["layui" => "input", "title" => "系统名称",
                        "name"   => "LC[title]",
                        "value"  => $config['title'],
                        "verify" => "required",
                    ],
                    ["layui" => "radio", "title" => "访问协议",
                        "name"   => "LC[https]",
                        "value"  => $config['https'] ?: 0,
                        "radio"  => [
                            ["title" => "自动判断", "value" => 0],
                            ["title" => "https://", "value" => 1],
                        ],
                        "tips"   => "一般请选自动判断",
                    ],
                    ["layui"      => "input", "title" => "后台域名",
                        "name"        => "LC[domain]",
                        "value"       => $config['domain'],
                        "placeholder" => "不填任意域名可访问后台，填写后仅填写的域名可以打开后台",
                        "tips"        => "不填任意域名可访问后台，填写后仅填写的域名可以打开后台",
                    ],
                    ["layui" => "input", "title" => "页脚版权",
                        "name"   => "LC[developer]",
                        "value"  => $config['developer'],
                        "verify" => "required"],
                    ["layui" => "upload", "title" => "浏览器图标",
                        "name"   => "LC[favicon]",
                        "value"  => $config['favicon'] ?: "/public/static/images/favicon.ico",
                        "accept" => ".png",
                        "local"  => true,
                        "width"  => 144,
                        "height" => 144,
                        "tips"   => "请上传png格式图片"],
                    ["layui" => "upload", "title" => "登录LOGO",
                        "name"   => "LC[login_logo]",
                        "value"  => $config['login_logo'],
                        "local"  => true],
                    ["layui" => "upload", "title" => "登录背景",
                        "name"   => "LC[login_background]",
                        "value"  => $config['login_background'],
                        "local"  => true,
                        "tips"   => "推荐尺寸1920*1080"],
                    ["layui" => "editor", "title" => "后台公告",
                        "name"   => "LC[gonggao]",
                        "value"  => $config['gonggao']],
                    ["layui" => "btn", "title" => "立即保存"],
                );
                require LCMS::template("own/admin_index");
                break;
        }
    }
    public function doweb()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                if (!in_string($LC['domain'], ["http://", "https://"])) {
                    $LC['domain'] = trim($LC['domain'], "/");
                    $LC['domain'] = "http://{$LC['domain']}";
                }
                $domain       = parse_url($LC['domain']);
                $LC['https']  = $domain['scheme'] === "https" ? 1 : 0;
                $LC['domain'] = realhost($domain['host']);
                if ($domain['port']) {
                    $LC['domain'] .= ":{$domain['port']}";
                }
                $LC['domain_api'] = trim($LC['domain_api'], "/");
                if (!in_string($LC['domain_api'], ["http://", "https://"])) {
                    $LC['domain_api'] = "http://{$LC['domain_api']}";
                }
                $LC['domain_api'] .= "/";
                $LC['domain_api'] = realhost($LC['domain_api']);
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "web",
                    "form" => $LC,
                ]);
                ajaxout(1, "保存成功", "reload");
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "web",
                ]);
                $scheme = $config['https'] == "1" ? "https://" : "http://";
                $form   = [
                    ["layui" => "radio", "title" => "限制访问？",
                        "name"   => "LC[domain_must]",
                        "value"  => $config['domain_must'] ?: 0,
                        "tips"   => "限制前端只能通过下方域名访问，除非你特别懂，否则请不要限制",
                        "radio"  => [
                            ["title" => "限制域名", "value" => 1],
                            ["title" => "不限制域名", "value" => 0],
                        ]],
                    ["layui"      => "input", "title" => "默认前端域名",
                        "name"        => "LC[domain]",
                        "value"       => $config['domain'] ? "{$scheme}{$config['domain']}/" : "",
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意：开头的 http:// 或 https:// 和结尾的 / 斜杠，要写完整。",
                        "verify"      => "required"],
                    ["layui"      => "input", "title" => "默认API域名",
                        "name"        => "LC[domain_api]",
                        "value"       => $config['domain_api'],
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意：开头的 http:// 或 https:// 和结尾的 / 斜杠，要写完整。",
                        "verify"      => "required"],
                    ["layui" => "input", "title" => "默认前端Title",
                        "name"   => "LC[title]",
                        "value"  => $config['title']],
                    ["layui" => "upload", "title" => "默认前端图片",
                        "name"   => "LC[image_default]",
                        "value"  => $config['image_default']],
                    ["layui" => "textarea", "title" => "平台统计代码",
                        "name"   => "LC[tongji]",
                        "value"  => $config['tongji']],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                if (!LCMS::SUPER()) {
                    $form = array_map(function ($val) {
                        unset($val['verify']);
                        return $val;
                    }, $form);
                }
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
                if (in_array(strtolower($LC['dir']), [
                    "admin", "wp-login", "manage", "manager", "member",
                ])) {
                    ajaxout(0, "后台目录不能使用{$LC['dir']}");
                }
                if (mb_strlen($LC['dir']) < 5) {
                    ajaxout(0, "后台目录最少需要5个字符");
                }
                if ($LC['dir'] != $_L['config']['admin']['dir']) {
                    if (!getdirpower(PATH_WEB)) {
                        ajaxout(0, "根目录没有写权限");
                    } else {
                        $change = true;
                    }
                }
                if ($LC['mimelist']) {
                    $LC['mimelist'] = str_replace([".", ","], "", $LC['mimelist']);
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
            case 'reloginkey':
                $loginkey = randstr(32);
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
                    "form" => [
                        "loginbytoken" => 1,
                        "loginkey"     => $loginkey,
                    ],
                    "lcms" => true,
                ]);
                ajaxout(1, "success", "", $loginkey);
                break;
            default:
                $config = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                $form = [
                    ["layui" => "title", "title" => "后台安全"],
                    ["layui"    => "input_sort", "title" => "后台目录",
                        "name"      => "LC[dir]",
                        "value"     => $config['dir'] ?: "admin",
                        "minlength" => 6,
                        "tips"      => "修改后台目录提高安全性，最少5个字符",
                        "verify"    => "required"],
                    ["layui" => "radio", "title" => "登录限制",
                        "name"   => "LC[login_limit]",
                        "value"  => $config['login_limit'] ?? 1,
                        "tips"   => "一个账号可否同时在多个设备登录",
                        "radio"  => [
                            ["title" => "单设备", "value" => 0],
                            ["title" => "多设备", "value" => 1],
                        ]],
                    ["layui"  => "slider", "title" => "自动登出",
                        "name"    => "LC[sessiontime]",
                        "value"   => $config['sessiontime'] ?: 0,
                        "min"     => 0,
                        "max"     => 120,
                        "step"    => 10,
                        "settips" => "分钟",
                        "tips"    => "超时无操作自动登出，0为不自动登出"],
                    ["layui" => "radio", "title" => "网站登录器",
                        "name"   => "LC[loginbytoken]",
                        "value"  => $config['loginbytoken'] ?: 0,
                        "tips"   => "是否开启网站登录器一键登录网站，需配合其它工具使用！",
                        "radio"  => [
                            ["title" => "开启", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ]],
                    ["layui" => "html", "title" => "登录密钥",
                        "value"  => '<span class="lcms-form-copy" data-copytext="' . $config['loginkey'] . '" style="margin-right:20px">' . strstar($config['loginkey'], 4) . '</span><a style="margin-right:20px" href="javascript:reLoginKey()">' . ($config['loginkey'] ? "重新生成" : "立即生成") . '</a>' . ($config['loginkey'] ? '</span><a href="javascript:copyLoginKey(\'' . base64_encode(json_encode_ex([
                            "title" => $config['title'],
                            "link"  => $_L['url']['admin'],
                            "name"  => SESSION::get("LCMSADMIN")['name'],
                            "token" => $config['loginkey'],
                        ])) . '\')">一键复制</a>' : '')],
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
                    ["layui" => "input_sort", "title" => "图片大小限制",
                        "name"   => "LC[attsize]",
                        "value"  => $config['attsize'] ?: 300,
                        "tips"   => "KB，限制上传图片的大小，超过的图片会自动压缩",
                        "verify" => "required"],
                    ["layui" => "input_sort", "title" => "文件大小限制",
                        "name"   => "LC[attsize_file]",
                        "value"  => $config['attsize_file'] ?: 300,
                        "tips"   => "KB，限制上传文件的大小，上传视频等大文件请开启云存储",
                        "verify" => "required"],
                    ["layui" => "des", "title" => "特别注意：为了后台安全，请在上传完文件后，及时删除不常用的后缀。"],
                    ["layui" => "tags", "title" => "格式白名单",
                        "name"   => "LC[mimelist]",
                        "value"  => $_L['config']['admin']['mimelist'],
                        "tips"   => "上传格式白名单，例如jpg，不需要加.点",
                        "verify" => "required"],
                    ["layui" => "title", "title" => "图片转换"],
                    ["layui" => "des", "title" => "▲ 图片转WEBP：是否将上传图片自动转为webp格式，在保留图片清晰度的前提下，文件可缩小5-10倍。很老的浏览器将无法看到图片内容，如果开启后无法上传图片，请关闭此功能！<br>▲ 缩略图WEBP：可自动判断浏览器是否支持webp格式图片，而输出对应格式缩略图。如开启后网站缩略图无法显示，请关闭此功能！"],
                    ["layui" => "radio", "title" => "图片转WEBP",
                        "name"   => "LC[attwebp]",
                        "value"  => $config['attwebp'] ?: 0,
                        "radio"  => [
                            ["title" => "开启", "value" => 1],
                            ["title" => "关闭", "value" => 0],
                        ]],
                    ["layui" => "radio", "title" => "缩略图WEBP",
                        "name"   => "LC[thumbwebp]",
                        "value"  => $config['thumbwebp'] ?: 0,
                        "radio"  => [
                            ["title" => "自动判断", "value" => 0],
                            ["title" => "关闭", "value" => 1],
                        ]],
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
                    function_exists("opcache_get_status") && opcache_reset();
                }
                if ($LF['cfg']) {
                    delfile(PATH_CORE . "asynced");
                    sql_query("TRUNCATE {$_L['table']['cache']}");
                }
                if ($LF['static']) {
                    deldir(PATH_CACHE . "static");
                }
                ajaxout(1, "清除成功", "close");
                break;
            default:
                $form = [
                    ["layui"   => "checkbox", "title" => "缓存类型",
                        "checkbox" => [
                            ["title" => "模板缓存", "name" => "tpl", "value" => 0],
                            ["title" => "配置缓存", "name" => "cfg", "value" => 0],
                            ["title" => "本地缓存", "name" => "browser", "value" => 1],
                            ["title" => "CSS/JS缓存", "name" => "static", "value" => 0],
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
            $redisid = "lcms-sys-redistest-" . md5($_L['url']['site']);
            LOAD::plugin("Redis/rds");
            $redis = new RDS();
            $redis->do->setex($redisid, 60, "success");
            if ($redis->do->get($redisid) === "success") {
                ajaxout(1);
            } else {
                ajaxout(0, "Redis未成功开启");
            }
        } else {
            ajaxout(1);
        }
    }
}
