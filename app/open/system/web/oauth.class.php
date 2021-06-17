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
        $config = $_L['config']['admin'];
        if ($config['oauth_code']) {
            ajaxout(1, "success", "", [
                "code" => $config['oauth_code'],
            ]);
        }
    }
}
