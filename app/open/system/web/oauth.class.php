<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('webbase');
class oauth extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function docode()
    {
        global $_L;
        $code = LCMS::cache("appstore/install_code");
        if ($code) {
            ajaxout(1, "success", "", $code[0]);
        }
    }
}
