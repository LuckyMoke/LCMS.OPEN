<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2022-09-06 21:55:48
 * @Description: 前端模板静态文件处理
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class TPL
{
    public static $cache   = 1;
    public static $ver     = "";
    public static $scanarr = "";
    public static $tplpath = "";
    /**
     * @description: 缓存文件名称
     * @param {*}
     * @return string
     */
    public static function cachename()
    {
        global $_L;
        $cachefile = PATH_CACHE . "static/" . substr(md5(L_NAME . self::$tplpath . self::$ver), 8, 16);
        return $cachefile;
    }
    /**
     * @description: 生成css，js文件
     * @param array $paths
     * @return {*}
     */
    public static function getui($paths = [])
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
        $tpath = PATH_APP_NOW . "web/tpl" . (self::$tplpath ? "/" . self::$tplpath : "");
        if (self::$cache == 2) {
            $_L['ui']['nocache'] = 1;
            foreach ($paths as $val) {
                if ($val) {
                    $val = explode("?", $val)[0];
                    if (stripos($val, PATH_WEB) === false && !is_url($val)) {
                        $val = "{$tpath}/{$val}";
                    }
                    $suffix = substr($val, strrpos($val, ".") + 1);
                    if ($suffix === "css") {
                        $_L['ui']['css'][] = $val;
                    } elseif ($suffix === "js") {
                        if (preg_match("/jquery+(-|.?)+(.*).js/i", $val)) {
                            $_L['ui']['js-head'][] = $val;
                        } else {
                            $_L['ui']['js'][] = $val;
                        }
                    } else {
                        $_L['ui']['xhr'][] = $val;
                    }
                }
            }
        } else {
            if (!is_file(self::cachename() . ".css") || !is_file(self::cachename() . ".js") || !self::$cache) {
                $files = self::scanAll($tpath) ?: [];
                foreach ($paths as $index => $val) {
                    if (stripos($val, PATH_WEB) === false) {
                        $paths[$index] = "{$tpath}/{$val}";
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
        }
    }
    /**
     * [patch 对文件内容做处理]
     * @param  array $paths  [description]
     * @param  string $suffix [description]
     * @return [type]         [description]
     */
    public static function patch($paths, $suffix)
    {
        global $_M;
        $cachefile = self::cachename() . ".{$suffix}";
        $code      = self::get_content($paths, $suffix);
        $codes     = "";
        foreach ($code as $val) {
            if ($val['code']) {
                $val['name'] = $val['name'] ? $val['name'] : "base";
                $codes .= "/*{$val['name']}*/\n{$val['code']}\n";
            }
        }
        makedir(PATH_CACHE . "static/");
        file_put_contents($cachefile, $codes);
    }
    /**
     * [get_content 读取css，js文件内容]
     * @param  array $paths  [description]
     * @param  string $suffix [description]
     * @return [type]         [description]
     */
    public static function get_content($paths, $suffix)
    {
        $code = array();
        foreach ($paths as $file) {
            if (is_file($file)) {
                $codea         = array();
                $codea['name'] = pathinfo($file, PATHINFO_BASENAME);
                $codea['code'] = self::ps_content($file, $suffix);
                $code[]        = $codea;
            }
        }
        return $code;
    }
    /**
     * [ps_content 对文件内容做处理]
     * @param  string $file   [description]
     * @param  string $suffix [description]
     * @return [type]         [description]
     */
    public static function ps_content($file, $suffix)
    {
        global $_L;
        $code = file_get_contents($file);
        $file = str_replace(PATH_WEB, "", $file);
        if ($suffix === 'css') {
            $adurl = "../../" . dirname($file) . '/';
            preg_match_all("/(?<=url\()[^\)]+/i", $code, $urls);
            $urls = $urls[0] ?: [];
            $urls = array_unique($urls);
            foreach ($urls as $url) {
                if (!in_string($url, "data:")) {
                    $code = str_replace("($url)", "({$adurl}{$url})", $code);
                }
            }
        }
        if ($suffix === 'js') {
            // JS过滤规则
        }
        $code = str_replace(["  ", "\t", "\n", "\r"], "", $code);
        return $code;
    }
    /**
     * [scanAll 扫描所有文件]
     * @param  string $dir [description]
     * @param  array  $arr [description]
     * @return [type]      [description]
     */
    public static function scanAll($dir, $arr = array())
    {
        self::$scanarr = array_merge_recursive($arr, glob($dir . "/*.css"), glob($dir . "/*.js"));
        foreach (scandir($dir) as $val) {
            if ($val !== '.' && $val !== '..' && is_dir($dir . '/' . $val)) {
                self::scanAll($dir . '/' . $val, self::$scanarr);
            }
        }
        return self::$scanarr;
    }
}
