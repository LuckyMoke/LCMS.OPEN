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
            LCMS::X(403, "缺少必要参数");
        }
        header("cache-control: max-age=3600");
        $para = stristr($_L['form']['para'], ".", true);
        $para = $para ? $para : $_L['form']['para'];
        $para = explode("|", ssl_decode($para));
        $path = path_absolute($para[0]);
        if (!is_file($path) || is_dir($path)) {
            $path = $_L['config']['web']['image_default'] ? path_absolute($_L['config']['web']['image_default']) : LCMS::X(404, "图片不存在");
        }
        THUMB::create($path, $para[1], $para[2]);
    }
};
