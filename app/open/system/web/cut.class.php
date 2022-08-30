<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("thumb");
class cut extends webbase
{
    public function __construct()
    {
        global $_L, $LF;
        parent::__construct();
        $LF = $_L['form'];
    }
    public function doindex()
    {
        global $_L, $LF;
        if (!$LF['para']) {
            header("HTTP/1.1 403 Forbidden");
            LCMS::X(403, "拒绝访问");
            exit;
        }
        list($x, $y, $path) = explode(",", $LF['para']);
        $path               = path_absolute("../{$path}");
        $path               = is_file($path) ? $path : $_L['config']['web']['image_default'];
        if (!is_file($path)) {
            header("HTTP/1.1 404 Not Found");
            LCMS::X(404, "未找到图片");
            exit;
        }
        header("cache-control: public, max-age=604800");
        header("pragma: cache");
        header("expires: " . gmdate("D, d M Y H:i:s", time() + 604800) . " GMT");
        header("last-modified: Mon, 26 Jul 1997 05:00:00 GMT");
        THUMB::create($path, $x, $y);
    }
}
