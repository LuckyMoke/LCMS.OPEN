<?php
namespace Wechatlogin;

defined('IN_LCMS') or exit('No permission');
class WechatPluins
{
    public function MsgHandle($post = [])
    {
        global $_L;
        switch ($post['type']) {
            case 'scan':
                $rawdata = $post['rawdata'];
                if (in_string($rawdata['EventKey'], "LOGIN|")) {
                    $keys = explode("|", $rawdata['EventKey']);
                    if ($keys[1] == $_L['ROOTID']) {
                        session_name("LCMSSID");
                        session_id($keys[2]);
                        session_start();
                        $url = $_SESSION['LOGINQRCODE'];
                        if ($url) {
                            unset($_SESSION['LOGINQRCODE']);
                            return [
                                "msgtype" => "news",
                                "news"    => [
                                    "articles" => [[
                                        "title"       => "用户登录",
                                        "description" => "点击此链接，选择需要登录的用户。",
                                        "url"         => $url,
                                        "picurl"      => "{$_L['url']['site']}/app/sys/login/admin/tpl/default/static/login.gif",
                                    ]],
                                ],
                            ];
                        }
                    }
                }
                break;
        }
    }
}
