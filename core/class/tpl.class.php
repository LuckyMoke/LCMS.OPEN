<?php
defined('IN_LCMS') or exit('No permission');
class TPL
{
    public static $cache   = true;
    public static $ver     = "";
    public static $scanarr = "";
    public static $tplpath = "";
    /**
     * [cachename 缓存文件名称]
     * @return [type] [description]
     */
    public static function cachename()
    {
        global $_L;
        $cachefile = PATH_CACHE . "static/" . md5(L_NAME . TPL::$ver);
        return $cachefile;
    }
    /**
     * [getui 获取css，js文件]
     * @param  [type] $paths [description]
     * @return [type]        [description]
     */
    public static function getui($paths)
    {
        global $_L;
        if (!file_exists(self::cachename() . ".css") || !file_exists(self::cachename() . ".js") || !self::$cache) {
            $files = self::scanAll(PATH_APP_NOW . "web/tpl" . (self::$tplpath ? "/" . self::$tplpath : ""));
            $files = array_merge_recursive($paths, $files);
            foreach ($files as $val) {
                $hz = pathinfo($val, PATHINFO_EXTENSION);
                if ($hz == 'css') {
                    $css[] = $val;
                }
                if ($hz == 'js') {
                    $js[] = $val;
                }
            }
            self::uicss($css);
            self::uijs($js);
        }
    }
    public static function uicss($paths)
    {
        self::patch($paths, "css");
    }
    public static function uijs($paths)
    {
        self::patch($paths, "js");
    }
    /**
     * [patch 对文件内容做处理]
     * @param  [type] $paths  [description]
     * @param  [type] $suffix [description]
     * @return [type]         [description]
     */
    public static function patch($paths, $suffix)
    {
        global $_M;
        $cachefile = self::cachename() . ".{$suffix}";
        $code      = self::get_content($paths, $suffix);
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
     * @param  [type] $paths  [description]
     * @param  [type] $suffix [description]
     * @return [type]         [description]
     */
    public static function get_content($paths, $suffix)
    {
        $code = array();
        foreach ($paths as $val) {
            $codea         = array();
            $codea['name'] = pathinfo($val, PATHINFO_BASENAME);
            $codea['code'] = self::ps_content($val, $suffix);
            $code[]        = $codea;
        }
        return $code;
    }
    /**
     * [ps_content 对文件内容做处理]
     * @param  [type] $path   [description]
     * @param  [type] $suffix [description]
     * @return [type]         [description]
     */
    public static function ps_content($path, $suffix)
    {
        global $_L;
        $code = file_get_contents($path);
        $path = str_replace(PATH_WEB, "", $path);
        if ($suffix == 'css') {
            $adurl = "../../" . dirname($path) . '/';
            preg_match_all("/(?<=url\()[^\)]+/i", $code, $urls);
            foreach ($urls[0] as $url) {
                if (strpos($url, "data:image") === false) {
                    $code  = str_replace($url, "{$adurl}{$url}", $code);
                }
            }
        }
        if ($suffix == 'js') {

        }
        $code = str_replace(array("  ", "\t", "\n", "\r"), "", $code);
        return $code;
    }
    /**
     * [scanAll 扫描所有文件]
     * @param  [type] $dir [description]
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
