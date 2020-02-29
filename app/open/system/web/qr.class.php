<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::plugin('Qrcode/phpqrcode');
class qr extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
    	global $_L;
        if ($_L['form']['text']) {
            phpqrcode::png($_L['form']['text'], false, "L", 10, 1);
        } else {
            LCMS::X(403, "缺少必要参数，禁止访问");
        }
    }
};