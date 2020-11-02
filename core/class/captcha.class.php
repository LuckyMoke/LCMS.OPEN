<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-11-02 14:55:19
 * @Description:验证码生成类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class CAPTCHA
{
    public static function check($pin)
    {
        $authpin = self::getpin();
        if ($authpin && $authpin == strtoupper($pin)) {
            self::setpin(randstr(4, "num"));
            return true;
        } else {
            return false;
        }
    }
    public static function set()
    {
        self::getAuthImage(randstr(4, "num"));
    }
    public static function getAuthImage($text)
    {
        self::setpin($text);
        ob_end_clean();
        $im_x = 150;
        $im_y = 40;
        $im   = imagecreatetruecolor($im_x, $im_y);
        imagefill($im, 16, 13, ImageColorAllocate($im, 226, 245, 255));
        $font = PATH_PUBLIC . 'static/fonts/Chinese.ttf';

        for ($i = 0; $i < strlen($text); $i++) {
            imagettftext($im, 28, mt_rand(1, 10), 25 + $i * 28, mt_rand(30, 40), ImageColorAllocate($im, mt_rand(100, 200), 68, 139), $font, substr($text, $i, 1));
        }
        for ($i = 0; $i < 60; $i++) {
            $randcolor = ImageColorAllocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            imagettftext($im, mt_rand(6, 12), mt_rand(1, 20), mt_rand(0, $im_x), mt_rand(0, $im_y), $randcolor, $font, randstr(1));
        }
        $rand  = mt_rand(16, 20);
        $rand1 = mt_rand(10, 15);
        $rand2 = mt_rand(5, 15);
        $color = ImageColorAllocate($im, mt_rand(100, 200), 68, 139);
        for ($yy = $rand; $yy <= +$rand + 2; $yy++) {
            for ($px = -60; $px <= 60; $px = $px + 1.2) {
                $x = $px / $rand1;
                if ($x != 0) {
                    $y = sin($x);
                }
                $py = $y * $rand2;
                imagesetpixel($im, $px + 75, $py + $yy, $color);
            }
        }
        ob_end_clean();
        header("content-type: image/jpeg");
        imagejpeg($im);
        ImageDestroy($im);
    }
    public static function setpin($str)
    {
        return session::set('LCMSPINCODE', $str);
    }
    public static function getpin()
    {
        return session::get('LCMSPINCODE');
    }
}
