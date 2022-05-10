<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("thumb");
class cut extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        if (!$_L['form']['para']) {
            header("HTTP/1.1 403 Forbidden");
            LCMS::X(403, "拒绝访问");
            exit;
        }
        $para = stristr($_L['form']['para'], ".", true);
        $para = $para ? $para : $_L['form']['para'];
        $para = explode("|", base64_decode($para));
        $path = path_absolute($para[0]);
        if (!is_file($path) || is_dir($path)) {
            $path = $_L['config']['web']['image_default'] ? path_absolute($_L['config']['web']['image_default']) : header("HTTP/1.1 404 Not Found");
            LCMS::X(404, "未找到图片");
            exit;
        }
        header("cache-control: public, max-age=604800");
        header("pragma: cache");
        header("expires: " . gmdate("D, d M Y H:i:s", time() + 604800) . " GMT");
        header("last-modified: Mon, 26 Jul 1997 05:00:00 GMT");
        THUMB::create($path, $para[1], $para[2]);
    }
}
