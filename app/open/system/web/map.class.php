<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('webbase');
class map extends webbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $plugin = LCMS::config([
            "do"   => "get",
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
            "lcms" => true,
        ]);
        if (
            $plugin['map'] &&
            $plugin['map']['tianditu'] &&
            $plugin['map']['tianditu']['key']
        ) {
            ajaxout(1, "success", "", $plugin['map']['tianditu']['key']);
        }
        ajaxout(0, "error");
    }
}