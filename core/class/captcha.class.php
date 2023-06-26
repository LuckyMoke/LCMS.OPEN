<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-06-25 16:22:47
 * @Description:验证码生成类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class CAPTCHA
{
    /**
     * @description: 检查验证码
     * @param string $pin
     * @return bool
     */
    public static function check($pin)
    {
        $authpin = self::getpin();
        if ($authpin && $authpin == strtoupper($pin)) {
            self::setpin(self::getRandNumber(4));
            return true;
        }
        return false;
    }
    public static function set()
    {
        self::getAuthImage(self::getRandNumber(4));
    }
    public static function getAuthImage($text)
    {
        self::setpin($text);
        ob_end_clean();
        $im_x = 200;
        $im_y = 55;
        $im   = imagecreatetruecolor($im_x, $im_y);
        imagefill($im, 16, 13, ImageColorAllocate($im, 226, 245, 255));
        $font = PATH_PUBLIC . 'static/fonts/Captcha.ttf';
        for ($i = 0; $i < 20; $i++) {
            $randcolor = ImageColorAllocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            imagettftext($im, mt_rand(20, 24), 20, mt_rand(0, $im_x), mt_rand(0, $im_y + 20), $randcolor, $font, self::getRandNumber(1));
        }
        for ($i = 0; $i < strlen($text); $i++) {
            imagettftext($im, 38, mt_rand(1, 20), 20 + ($i * 38), mt_rand(45, 55), ImageColorAllocate($im, mt_rand(100, 200), 68, 139), $font, substr($text, $i, 1));
        }
        $rand  = mt_rand(25, 30);
        $rand1 = mt_rand(10, 25);
        $rand2 = mt_rand(10, 20);
        $color = ImageColorAllocate($im, mt_rand(100, 200), 68, 139);
        for ($yy = $rand; $yy <= +$rand + 3; $yy++) {
            for ($px = -80; $px <= 80; $px = $px + 1.2) {
                $x = $px / $rand1;
                if ($x != 0) {
                    $y = sin($x);
                }
                $py = $y * $rand2;
                imagesetpixel($im, $px + 100, $py + $yy, $color);
            }
        }
        ob_end_clean();
        header("content-type: image/jpeg");
        imagejpeg($im);
        ImageDestroy($im);
    }
    public static function setpin($str)
    {
        return SESSION::set('LCMSPINCODE', $str);
    }
    public static function getpin()
    {
        return SESSION::get('LCMSPINCODE');
    }
    private static function getRandNumber($length = 4)
    {
        $number = "023456789";
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $num[$i] = mt_rand(0, strlen($number) - 1);
            $result .= $number[$num[$i]];
        }
        return $result;
    }
}
