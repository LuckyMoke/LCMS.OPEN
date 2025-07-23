<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2025-07-08 11:30:33
 * @Description: 前端模板静态文件处理
 * @Copyright 2024 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class TPL
{
    public static $cache   = 1;
    public static $cname   = "";
    public static $ver     = "";
    public static $tplpath = "";
    public static $tpath   = "";
    public static function init($paths = [])
    {
        global $_L;
        $paths    = $paths ?: [];
        $_L['ui'] = array_merge([
            "nocache" => 0,
            "css"     => [],
            "js-head" => [],
            "js"      => [],
            "xhr"     => [],
        ], $_L['ui'] ?? []);
        $cname       = self::setCname();
        self::$tpath = PATH_APP_NOW . "web/tpl" . (self::$tplpath ? "/" . self::$tplpath : "");
        switch (self::$cache) {
            case 2:
                $_L['ui']['nocache'] = 1;
                foreach ($paths as $val) {
                    if ($val) {
                        $val = explode("?", $val)[0];
                        if (!is_url($val) && !in_string($val, PATH_WEB)) {
                            $val = self::$tpath . "/{$val}";
                        }
                        $suffix = substr($val, strrpos($val, ".") + 1);
                        switch ($suffix) {
                            case 'css':
                                $_L['ui']['css'][] = $val;
                                break;
                            case 'js':
                                if (preg_match("/jquery+(-|.?)+(.*).js/i", $val)) {
                                    $_L['ui']['js-head'][] = $val;
                                } else {
                                    $_L['ui']['js'][] = $val;
                                }
                                break;
                            default:
                                $_L['ui']['xhr'][] = $val;
                                break;
                        }
                    }
                }
                break;
            default:
                if (!self::$cache || !is_file("{$cname}.css") || !is_file("{$cname}.js")) {
                    $files = self::scanAll(self::$tpath);
                    foreach ($paths as $index => $val) {
                        if (!in_string($val, PATH_WEB)) {
                            $paths[$index] = self::$tpath . "/{$val}";
                        }
                    }
                    $files = array_diff($files, $paths);
                    $files = array_merge_recursive($paths, $files);
                    foreach ($files as $val) {
                        $suffix = pathinfo($val, PATHINFO_EXTENSION);
                        if ($suffix === 'css') {
                            $css[] = $val;
                        }
                        if ($suffix === 'js') {
                            $js[] = $val;
                        }
                    }
                    self::patch($css, "css");
                    self::patch($js, "js");
                }
                break;
        }
    }
    /**
     * @description: 获取缓存文件名称
     * @return string
     */
    private static function setCname()
    {
        self::$cname = PATH_CACHE . "static/" . substr(md5(L_NAME . self::$tplpath . self::$ver), 8, 16);
        return self::$cname;
    }
    /**
     * @description: 初始化css，js文件
     * @param array $paths
     * @return {*}
     */
    /**
     * @description: 对文件进行合并处理
     * @param array $paths
     * @param string $suffix
     * @return {*}
     */
    private static function patch($paths, $suffix)
    {
        global $_L;
        $codes = "";
        foreach ($paths as $file) {
            if (is_file($file)) {
                $name = pathinfo($file, PATHINFO_BASENAME);
                $name = md5($name ?: "base");
                $code = self::getContent($file, $suffix);
                $codes .= "/**\n* @ {$name}\n*/\n{$code}\n";
            }
        }
        makedir(PATH_CACHE . "static/");
        file_put_contents(self::$cname . ".{$suffix}", $codes);
    }
    /**
     * @description: 对文件内容做处理
     * @param string $file
     * @param string $suffix
     * @return {*}
     */
    private static function getContent($file, $suffix)
    {
        global $_L;
        $code = file_get_contents($file);
        $file = str_replace(PATH_WEB, "", $file);
        switch ($suffix) {
            case 'css':
                $adurl = "../../" . dirname($file) . '/';
                preg_match_all("/(?<=url\()[^\)]+/i", $code, $urls);
                $urls = $urls[0] ?: [];
                $urls = array_unique($urls);
                foreach ($urls as $url) {
                    if (!in_string($url, "data:")) {
                        $code = str_replace("($url)", "({$adurl}{$url})", $code);
                    }
                }
                break;
            case 'js':
                preg_match_all("/(?!\\\|\"|'|:)(.|^|\n)\/\/(.*)[\n]/i", $code, $notes);
                $notes = $notes[0] ?: [];
                $notes = array_unique($notes);
                foreach ($notes as $note) {
                    if (mb_strlen($note) < 1000) {
                        $code = str_replace($note, str_replace("\n", "[LCMSJSENCODEENTER]", $note), $code);
                    }
                }
                break;
        }
        return str_replace([
            "  ", "\t", "\n", "\r", "[LCMSJSENCODEENTER]",
        ], [
            "", "", "", "", "\n",
        ], $code);
    }
    /**
     * @description: 扫描所有文件
     * @param string $path
     * @param array $files
     * @return array
     */
    private static function scanAll($path, $files = [])
    {
        global $_L;
        if ($path == (self::$tpath . "/static/nocache")) {
            return $files ?: [];
        }
        $files = array_merge_recursive($files, glob("{$path}/*.css"), glob("{$path}/*.js"));
        foreach (scandir($path) as $file) {
            if ($file !== "." && $file !== ".." && is_dir("{$path}/{$file}")) {
                $files = self::scanAll("{$path}/{$file}", $files);
            }
        }
        return $files ?: [];
    }
}
