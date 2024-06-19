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
        $_L['form']['token'] || $this->stop();
        $_L['form']['text'] || $this->stop();
        $token = ssl_decode_gzip($_L['form']['token'], "qrcode");
        $token < time() && $this->stop();
        phpqrcode::png($_L['form']['text'], false, "H", 10, 1);
    }
    private function stop()
    {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
};
