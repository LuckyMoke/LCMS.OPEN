<?php
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\ChallengeOptions;

class AltchaCaptcha
{
    private $path = PATH_CORE_PLUGIN . "Altcha/";
    private $php  = "php7";
    public function __construct()
    {
        global $_L;
        require_once "{$this->path}libs/altcha.phar";
        if (version_compare(PHP_VERSION, "8.1", "<")) {
            $this->php = "php7";
        } else {
            $this->php = "php8";
        }
    }
    /**
     * @description: 获取验证html
     * @param {*} $args [challengeurl、onchange]
     * @return {*}
     */
    public function html($args = [])
    {
        global $_L;
        SESSION::set("lcms:altcha:hmacKey", uuid_create());
        $args = array_merge([
            "id"               => "altcha-" . randstr(4),
            "name"             => "altcha",
            "auto"             => "off",
            "hidefooter"       => true,
            "hidelogo"         => true,
            "disableautofocus" => true,
            "count"            => 3,
            "code"             => false,
        ], $args);
        $count = LCMS::ram("lcms/plugin:altcha", "", 0, true);
        $count = $count ? intval($count) : 0;
        if ($count > $args['count'] || $args['code']) {
            $args['verifyurl'] = "{$_L['url']['own']}n=system&c=pin&a=altcha&action=verify-image";
            SESSION::set("lcms:altcha:image", 0);
        } else {
            SESSION::set("lcms:altcha:image", 1);
        }
        $onchange = $args['onchange'];
        unset($args['onchange'], $args['count'], $args['code']);
        if ($onchange) {
            $onchange = "<script type=\"text/javascript\">document.querySelector(`#{$args['id']}`).addEventListener(`statechange`,(e=>{if(`verified`===e.detail.state){if(typeof {$onchange}==`function`){{$onchange}(e.detail.payload)}}}));</script>";
        }
        $config = [];
        foreach ($args as $key => $val) {
            if (empty($val)) {
                continue;
            }
            if (is_bool($val)) {
                $val = $val ? "true" : "false";
            }
            $config[] = "{$key}=\"{$val}\"";
        }
        $config = implode(" ", $config);
        $html   = "<style type=\"text/css\">.altcha-code-challenge-image{object-fit:fill!important}</style><altcha-widget class=\"lcms-altcha\" {$config}></altcha-widget><script src=\"{$_L['url']['site']}core/plugin/Altcha/static/lang.min.js?lcmsver={$_L['config']['ver']}\" async defer type=\"module\"></script><script src=\"{$_L['url']['site']}core/plugin/Altcha/static/altcha.min.js?lcmsver={$_L['config']['ver']}\" async defer type=\"module\"></script>{$onchange}";
        return $html;
    }
    /**
     * @description: 生成挑战
     * @param string $hmacKey 密钥
     * @return {*}
     */
    public function challenge()
    {
        global $_L;
        $hmacKey = SESSION::get("lcms:altcha:hmacKey");
        if (empty($hmacKey)) return;
        $image = SESSION::get("lcms:altcha:image");
        if ($image != 1) {
            $expire = 30;
        } else {
            $expire = 10;
        }
        switch ($this->php) {
            case 'php7':
                $options = new ChallengeOptions([
                    "hmacKey"   => $hmacKey,
                    "maxNumber" => 50000,
                    "expires"   => (new \DateTimeImmutable())->add(new \DateInterval("PT{$expire}S")),
                    "saltLength" => 12,
                ]);
                $challenge = Altcha::createChallenge($options);
                break;
            case 'php8':
                $altcha  = new Altcha($hmacKey);
                $options = new ChallengeOptions(
                    AltchaOrg\Altcha\Hasher\Algorithm::SHA256,
                    50000,
                    (new \DateTimeImmutable())->add(new \DateInterval("PT{$expire}S")),
                    [],
                    12,
                );
                $challenge = $altcha->createChallenge($options);
                break;
        }
        $challenge = json_encode($challenge);
        $challenge = json_decode($challenge, true);
        //添加验证码
        $challenge['codeChallenge'] = [
            "image"  => $_L['url']['captcha'] . time(),
            "length" => 4,
        ];
        $count = LCMS::ram("lcms/plugin:altcha", "", 0, true);
        $count = $count ? intval($count) : 0;
        $time  = 60 - date("s");
        LCMS::ram("lcms/plugin:altcha", $count + 1, $time);
        return $challenge;
    }
    /**
     * @description: 验证
     * @param {*} $hmacKey
     * @param {*} $payload
     * @return {*}
     */
    public function verify($payload = "")
    {
        if (empty($payload)) return;
        $hmacKey = SESSION::get("lcms:altcha:hmacKey");
        if (empty($hmacKey)) return;
        $image = SESSION::get("lcms:altcha:image");
        if ($image != 1) return;
        switch ($this->php) {
            case 'php7':
                if (Altcha::verifySolution($payload, $hmacKey, true)) {
                    return true;
                }
                break;
            case 'php8':
                $altcha = new Altcha($hmacKey);
                if ($altcha->verifySolution($payload, true)) {
                    return true;
                }
                break;
        }
        return;
    }
}
