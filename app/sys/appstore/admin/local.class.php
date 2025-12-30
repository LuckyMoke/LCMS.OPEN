<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-12-29 20:22:11
 * @Description:本地应用列表
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class local extends adminbase
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
        require LCMS::template("own/local/index");
    }
    /**
     * @description: 设置默认应用
     * @param {*}
     * @return {*}
     */
    public function dosetdefault()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'save':
                if ($LC['token']) {
                    $lcms = ssl_decode($LC['token']);
                } else {
                    $lcms = $_L['ROOTID'];
                }
                $lcms = intval($lcms);
                $lcms <= 0 && LCMS::X(404, "参数错误");
                LCMS::config([
                    "do"   => "save",
                    "name" => "menu",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => $lcms,
                    "form" => [
                        "default" => [
                            "on"   => $LC['on'],
                            "name" => $LC['name'],
                        ],
                    ],
                ]);
                ajaxout(1, "保存成功", "close");
                break;
            default:
                if ($LF['token']) {
                    $lcms = ssl_decode($LF['token']);
                } else {
                    $lcms = $_L['ROOTID'];
                }
                $lcms = intval($lcms);
                $lcms <= 0 && LCMS::X(404, "参数错误");
                $open = LEVEL::applist("open", true);
                $open || LCMS::X(404, "您未安装任何应用");
                $config = LCMS::config([
                    "name" => "menu",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => $lcms,
                ]);
                foreach ($open as $name => $app) {
                    $applist[] = [
                        "title" => $app['title'],
                        "value" => $name,
                    ];
                }
                $form = [
                    ["layui" => "input", "type" => "hidden",
                        "name"   => "LC[token]",
                        "value"  => $LF['token']],
                    ["layui" => "radio", "title" => "默认应用",
                        "name"   => "LC[on]",
                        "value"  => $config['default']['on'] ?: 0,
                        "radio"  => [
                            ["title" => "欢迎页", "value" => 0, "tab" => "tab0"],
                            ["title" => "第一个应用", "value" => 1, "tab" => "tab1"],
                            ["title" => "指定应用", "value" => 2, "tab" => "tab2"],
                        ]],
                    ["layui" => "des", "title" => "登录后台默认打开欢迎页", "cname" => "hidden tab0"],
                    ["layui" => "des", "title" => "登录后台默认打开本地应用列表里的第一个应用", "cname" => "hidden tab1"],
                    ["layui" => "des", "title" => "登录后台默认打开下方选择的应用", "cname" => "hidden tab2"],
                    ["layui" => "select", "title" => "指定应用",
                        "name"   => "LC[name]",
                        "value"  => $config['default']['name'],
                        "cname"  => "hidden tab2",
                        "option" => $applist],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/local/default");
                break;
        }
    }
    /**
     * @description: 内部接口操作
     * @return {*}
     */
    public function doapi()
    {
        global $_L, $LF, $LC, $PRE;
        switch ($LF['action']) {
            case 'down':
                ignore_user_abort(true);
                set_time_limit(120);
                $token = ssl_decode($LF['token']);
                $token || ajaxout(0, "token错误");
                $token = json_decode($token, true);
                $token || ajaxout(0, "token错误");
                $file = "{$token['path']}{$token['name']}";
                makedir($token['path']);
                delfile($file);
                $result = HTTP::request([
                    "type"    => "DOWNLOAD",
                    "url"     => $token['url'],
                    "file"    => $file,
                    "timeout" => 120,
                ]);
                if ($result['code'] == 1) {
                    ajaxout(1, "success");
                } else {
                    ajaxout(0, "error");
                }
                break;
            case 'unzip':
                $token = ssl_decode($LF['token']);
                $token || ajaxout(0, "token错误");
                $token = json_decode($token, true);
                $token || ajaxout(0, "token错误");
                $file = "{$token['path']}{$token['name']}";
                if (
                    $token['name'] &&
                    unzipfile($file, "{$token['path']}unzip")
                ) {
                    movedir("{$token['path']}unzip", path_absolute($token['unpath']));
                    delfile($file);
                    ajaxout(1, "success");
                } else {
                    deldir($token['path']);
                    ajaxout(0, "解压文件失败");
                }
                break;
        }
    }
}
