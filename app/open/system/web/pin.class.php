<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("captcha");
class pin extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        captcha::set();
    }
};
