<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-03-07 15:50:06
 * @LastEditTime: 2024-05-28 10:34:47
 * @Description: 验证码生成类
 * Copyright 2024 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class CAPTCHA
{
    /**
     * @description: 设置验证码
     * @return {*}
     */
    public static function set()
    {
        if (in_string($_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI'])) {
            header("HTTP/1.1 403 Forbidden");
            exit();
        }
        $pin = randstr(4, "234567890");
        self::savePin($pin);
        self::createImage($pin);
    }
    /**
     * @description: 检查验证码
     * @param string $pin
     * @return bool
     */
    public static function check($pin)
    {
        $authpin = self::getPin();
        if ($authpin && $authpin == strtoupper($pin)) {
            self::delPin();
            return true;
        }
        return false;
    }
    /**
     * @description: 写入验证码
     * @param string $pin
     * @return string
     */
    public static function savePin($pin)
    {
        return SESSION::set('LCMSPINCODE', $pin);
    }
    /**
     * @description: 删除验证码
     * @return {*}
     */
    public static function delPin()
    {
        return SESSION::del('LCMSPINCODE');
    }
    /**
     * @description: 获取验证码
     * @return string
     */
    public static function getPin()
    {
        return SESSION::get('LCMSPINCODE');
    }
    /**
     * @description: 创建验证码图片
     * @param string $pin
     * @return {*}
     */
    private static function createImage($pin)
    {
        ob_end_clean();
        $im_x = 200;
        $im_y = 55;
        $im   = imagecreatetruecolor($im_x, $im_y);
        imagefill($im, 16, 13, ImageColorAllocate($im, 226, 245, 255));
        $font = PATH_PUBLIC . 'static/fonts/Captcha.ttf';
        for ($i = 0; $i < 20; $i++) {
            $randcolor = ImageColorAllocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            imagettftext($im, mt_rand(10, 14), 20, mt_rand(0, $im_x), mt_rand(0, $im_y + 20), $randcolor, $font, randstr(1, "num"));
        }
        $pyx = mt_rand(5, 45);
        for ($i = 0; $i < strlen($pin); $i++) {
            imagettftext($im, mt_rand(20, 24), mt_rand(-10, 20), $pyx + ($i * 40), mt_rand(45, 55), ImageColorAllocate($im, mt_rand(100, 200), 68, 139), $font, substr($pin, $i, 1));
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
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-type: image/jpeg");
        imagejpeg($im);
        ImageDestroy($im);
    }
}
