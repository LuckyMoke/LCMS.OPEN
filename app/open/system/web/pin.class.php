<?php
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class("webbase");
class pin extends webbase
{
    public function __construct()
    {
        global $_L, $LF;
        parent::__construct();
        $LF = $_L['form'];
    }
    public function doindex()
    {
        global $_L, $LF;
        LOAD::sys_class("captcha");
        CAPTCHA::set();
    }
    public function doaltcha()
    {
        global $_L, $LF;
        switch ($LF['action']) {
            case 'challenge':
                LOAD::plugin("Altcha/AltchaCaptcha");
                $AC        = new AltchaCaptcha();
                $challenge = $AC->challenge();
                header("Content-type: application/json");
                echo json_encode($challenge);
                break;
            case 'verify-image':
                $form = file_get_contents("php://input");
                if (empty($form)) return;
                $form = json_decode($form, true);
                if (empty($form['code'])) return;
                if (empty($form['payload'])) return;
                if (!is_numeric($form['code'])) return;
                header("Content-type: application/json");
                LOAD::sys_class("captcha");
                if (CAPTCHA::check($form['code'])) {
                    SESSION::set("lcms:altcha:image", 1);
                    echo json_encode([
                        "verified" => true,
                        "payload"  => $form['payload'],
                    ]);
                } else {
                    echo json_encode([
                        "verified" => false,
                    ]);
                };
                break;
            default:
                $LF['config'] || ajaxout(0, "未找到配置信息");
                LOAD::plugin("Altcha/AltchaCaptcha");
                $AC = new AltchaCaptcha();
                ajaxout(1, "success", "", $AC->html(array_merge([
                    "challengeurl" => "{$_L['url']['own']}n=system&c=pin&a=altcha&action=challenge",
                ], $LF['config'])));
                break;
        }
    }
};
