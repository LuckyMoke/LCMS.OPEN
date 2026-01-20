<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('webbase');
class oauth extends webbase
{
    public function __construct()
    {
        global $_L, $LF;
        parent::__construct();
        $LF = $_L['form'];
    }
    public function dosession()
    {
        global $_L, $LF;
        $LF['name'] || ajaxout(0, "error");
        $data = SESSION::get($LF['name']);
        ajaxout(1, "success", "", $data);
    }
    public function docode()
    {
        global $_L, $LF;
        $code = LCMS::cache("appstore/install_code");
        $code || ajaxout(0, "error");
        ajaxout(1, "success", "", $code[0]);
    }
}
