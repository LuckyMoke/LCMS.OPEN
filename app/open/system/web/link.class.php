<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("shortlink");
class link extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $code = $_SERVER['QUERY_STRING'];
        if (!$code) {
            header("HTTP/1.1 404 Not Found");
            LCMS::X(404, "Not Found");
            exit;
        }
        $link = SHORTLINK::get($code);
        if (!$link) {
            header("HTTP/1.1 404 Not Found");
            LCMS::X(404, "链接已失效");
            exit;
        }
        if ($link['data']) {
            okform($link['url'], $link['data']);
        } else {
            okinfo($link['url']);
        }
    }
};
