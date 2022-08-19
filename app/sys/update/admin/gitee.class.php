<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-11-02 15:07:46
 * @LastEditTime: 2022-08-17 12:55:12
 * @Description: Gitee升级功能
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class gitee extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC, $API, $CFG, $TOKEN;
        parent::__construct();
        $LF  = $_L['form'];
        $LC  = $LF['LC'];
        $API = "https://gitee.com/api/v5/repos/luckymoke/LCMS.OPEN/";
        $CFG = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        $TOKEN = "?access_token={$CFG['gitee_token']}";
    }
    public function doindex()
    {
        global $_L, $LF, $LC, $API, $CFG, $TOKEN;
        switch ($LF['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "name" => "config",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ]);
                ajaxout(1, "保存成功", "close");
                break;
            case 'setting':
                $form = [
                    ["layui" => "des", "title" => "【境内服务器】一般选“自动”。<br/>【境外服务器】若更新一半卡住，可挨个切换更新服务器进行尝试！如果都不行可将服务器主DNS改为 114.114.114.114（宝塔可直接在”软件商店->Linux工具箱“中设置），并将更新服务器设置为“自动”。"],
                    ["layui" => "radio", "title" => "更新服务器",
                        "name"   => "LC[gitee_server]",
                        "value"  => $CFG['gitee_server'] ?: 0,
                        "radio"  => [
                            ["title" => "自动", "value" => 0],
                            ["title" => "境内", "value" => 1],
                            ["title" => "香港", "value" => 2],
                            ["title" => "备用", "value" => 3],
                        ]],
                    ["layui"      => "input", "title" => "私人令牌",
                        "name"        => "LC[gitee_token]",
                        "value"       => $CFG['gitee_token'],
                        "placeholder" => "输入私人令牌后可进行在线更新操作",
                        "verify"      => "required"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/gitee/config");
                break;
            default:
                $version = $this->getVer();
                if (!$version) {
                    LCMS::X(403, "框架版本检测错误，请手动覆盖更新");
                }
                $powers = [
                    "app"    => getdirpower("/app") ? 1 : 0,
                    "cache"  => getdirpower("/cache") ? 1 : 0,
                    "core"   => getdirpower("/core") ? 1 : 0,
                    "upload" => getdirpower("/upload") ? 1 : 0,
                    "public" => getdirpower("/public") ? 1 : 0,
                ];
                $powertest = 0;
                foreach ($powers as $dir => $power) {
                    if ($power) {
                        $powers[$dir] = "<span style='color:green'><i class='layui-icon layui-icon-ok'></i>权限检测通过</span>";
                    } else {
                        $powertest    = 1;
                        $powers[$dir] = "<span style='color:red'><i class='layui-icon layui-icon-close'></i>无读写权限</span>";
                    }
                }
                require LCMS::template("own/gitee/index");
                break;
        }
    }
    public function docloud()
    {
        global $_L, $LF, $LC, $API, $CFG, $TOKEN;
        switch ($LF['action']) {
            case 'check':
                if (!$CFG['gitee_token']) {
                    ajaxout(2, "请先配置私人令牌");
                }
                $query  = "{$API}releases/latest{$TOKEN}";
                $result = $this->httpGet($query);
                $result = json_decode($result, true);
                if ($result['name']) {
                    ajaxout(1, "success", "", [
                        "version"          => $result['name'],
                        "status"           => $this->getVer() >= $result['tag_name'] ? 0 : 1,
                        "target_commitish" => $result['target_commitish'],
                    ]);
                } else {
                    ajaxout(0, "错误/" . ($result['message'] ?: "您的服务器无法访问更新服务器"));
                }
                break;
            case 'logs':
                if ($LF['target_commitish']) {
                    $query  = "{$API}releases/tags/{$this->getVer()}{$TOKEN}";
                    $result = $this->httpGet($query);
                    $result = json_decode($result, true);
                    if ($result['target_commitish']) {
                        $ocommit = $result['target_commitish'];
                    } elseif (is_file(PATH_CORE . "commit")) {
                        $ocommit = file_get_contents(PATH_CORE . "commit");
                    }
                    if ($ocommit) {
                        if ($ocommit == $LF['target_commitish']) {
                            ajaxout(1, "已是最新版");
                        }
                        $query  = "{$API}compare/{$ocommit}...{$LF['target_commitish']}{$TOKEN}";
                        $result = $this->httpGet($query);
                        $result = json_decode($result, true);
                        if ($result['commits'] || $result['files']) {
                            foreach ($result['commits'] as $val) {
                                $logs[] = [
                                    "time" => explode("T", $val['commit']['committer']['date'])[0],
                                    "info" => str_replace("\n", "<br/>", $val['commit']['message']),
                                ];
                            }
                            $logs = $logs ? array_unique($logs, SORT_REGULAR) : [];
                            rsort($logs);
                            ajaxout(1, "success", "", [
                                "logs"  => $logs,
                                "files" => $this->getFiles($result['files']),
                            ]);
                        }
                    }
                }
                ajaxout(0, $result['message'] ?: "更新失败");
                break;
            case 'down':
                $path   = PATH_CACHE . "update/LCMS.OPEN/";
                $query  = "{$API}git/blobs/{$LF['sha']}{$TOKEN}";
                $result = $this->httpGet($query);
                $result = json_decode($result, true);
                if ($result['content']) {
                    makedir($path . str_replace(end(explode('/', $LF['file'])), "", $LF['file']));
                    file_put_contents("{$path}{$LF['file']}", base64_decode($result['content']));
                    ajaxout(1, "成功");
                } else {
                    ajaxout(1, $result['message'] ?: "失败");
                }
                break;
            case 'copy':
                $path = PATH_CACHE . "update/LCMS.OPEN/";
                if (copydir($path, PATH_WEB)) {
                    deldir($path);
                };
                if ($LF['target_commitish']) {
                    file_put_contents(PATH_CORE . "commit", $LF['target_commitish']);
                }
                ajaxout(1, "success");
                break;
            case 'remove':
                delfile(PATH_WEB . $LF['file']);
                if ($CFG['dir'] && $CFG['dir'] != "admin") {
                    deldir(PATH_WEB . "admin/");
                    deldir(PATH_WEB . "install/");
                }
                ajaxout(1, "成功");
                break;
        }
    }
    /**
     * @description: 将变更文件分类
     * @param array $files
     * @return array
     */
    private function getFiles($files)
    {
        $jump = [
            "admin/",
            "install/",
            "favicon.ico",
            "LICENSE.md",
            "README.md",
        ];
        foreach ($files as $val) {
            $file = true;
            foreach ($jump as $file) {
                if (stripos($val['filename'], $file) === 0) {
                    $file = false;
                    break;
                }
            }
            if ($file == true) {
                $return[$val['status']][] = [
                    "sha"    => $val['sha'],
                    "status" => $val['status'],
                    "file"   => $val['filename'],
                ];
            }
        }
        return array_merge([
            "added"    => [],
            "modified" => [],
            "removed"  => [],
        ], $return ?: []);
    }
    /**
     * @description: 获取框架版本号
     * @param {*}
     * @return string
     */
    private function getVer()
    {
        global $_L, $LF, $LC, $API, $CFG, $TOKEN;
        return file_get_contents(PATH_CORE . "version");
    }
    /**
     * @description: HTTP请求
     * @param string $url
     * @param array $setIp
     * @return {*}
     */
    private function httpGet($url, $setIp = [])
    {
        global $_L, $LF, $LC, $API, $CFG, $TOKEN;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        switch ($CFG['gitee_server']) {
            case 1:
                $ip = "212.64.63.215";
                break;
            case 2:
                $ip = "154.213.2.253";
                break;
            case 3:
                $ip = "212.64.63.190";
                break;
        }
        $ip && curl_setopt($ch, CURLOPT_CONNECT_TO, ["gitee.com:443:{$ip}:443"]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
