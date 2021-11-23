<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-11-02 15:07:46
 * @LastEditTime: 2021-11-23 15:34:32
 * @Description: Gitee升级功能
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class gitee extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF        = $_L['form'];
        $LC        = $LF['LC'];
        $this->api = "https://gitee.com/api/v5/repos/luckymoke/LCMS.OPEN/";
        $this->cfg = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        $this->token = "?access_token={$this->cfg['gitee_token']}";
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
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
                    ["layui" => "input", "title" => "私人令牌",
                        "name"   => "LC[gitee_token]",
                        "value"  => $this->cfg['gitee_token'],
                        "verify" => "required"],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/gitee/config");
                break;
            default:
                if (!$this->ver()) {
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
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'check':
                if (!$this->cfg['gitee_token']) {
                    ajaxout(2, "请先配置私人令牌");
                }
                $query  = "{$this->api}releases/latest{$this->token}";
                $result = HTTP::get($query);
                $result = json_decode($result, true);
                if ($result['name']) {
                    ajaxout(1, "success", "", [
                        "version"          => $result['name'],
                        "status"           => $this->ver() >= $result['tag_name'] ? 0 : 1,
                        "target_commitish" => $result['target_commitish'],
                    ]);
                } else {
                    ajaxout(0, "错误/{$result['message']}");
                }
                break;
            case 'logs':
                if ($LF['target_commitish']) {
                    $query  = "{$this->api}releases/tags/{$this->ver()}{$this->token}";
                    $result = HTTP::get($query);
                    $result = json_decode($result, true);
                    if ($result['target_commitish']) {
                        $query  = "{$this->api}compare/{$result['target_commitish']}...{$LF['target_commitish']}{$this->token}";
                        $result = HTTP::get($query);
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
                                "files" => $this->get_files($result['files']),
                            ]);
                        }
                    }
                }
                ajaxout(0, $result['message'] ?: "更新失败");
                break;
            case 'down':
                $path   = PATH_CACHE . "update/LCMS.OPEN/";
                $query  = "{$this->api}git/blobs/{$LF['sha']}{$this->token}";
                $result = HTTP::get($query);
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
                ajaxout(1, "success");
                break;
            case 'remove':
                delfile(PATH_WEB . $LF['file']);
                if ($this->cfg['dir'] && $this->cfg['dir'] != "admin") {
                    deldir(PATH_WEB . "admin/");
                    deldir(PATH_WEB . "install/");
                }
                ajaxout(1, "成功");
                break;
        }
    }
    public function check()
    {
        global $_L, $LF, $LC;
    }
    /**
     * @description: 将变更文件分类
     * @param array $files
     * @return array
     */
    private function get_files($files)
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
    private function ver()
    {
        global $_L, $LF, $LC;
        return file_get_contents(PATH_CORE . "version");
    }
}
