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
        header("cache-control: max-age=604800");
        header("pragma: cache");
        header("expires: " . gmdate("D, d M Y H:i:s", time() + 604800) . " GMT");
    }
    public function doindex()
    {
        global $_L;
        if (!$_L['form']['para']) {
            LCMS::X(403, "缺少必要参数");
        }
        $para = stristr($_L['form']['para'], ".", true);
        $para = $para ? $para : $_L['form']['para'];
        $para = explode("|", base64_decode($para));
        $path = path_absolute($para[0]);
        if (!is_file($path) || is_dir($path)) {
            $path = $_L['config']['web']['image_default'] ? path_absolute($_L['config']['web']['image_default']) : LCMS::X(404, "图片不存在");
        }
        THUMB::create($path, $para[1], $para[2]);
    }
};
