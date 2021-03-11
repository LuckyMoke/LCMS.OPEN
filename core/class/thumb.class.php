<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-03-11 14:13:22
 * @Description:缩略图生成类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class THUMB
{
    public static function url($path = "", $x = "", $y = "", $html = false)
    {
        global $_L;
        if (is_url($path)) {
            // 如果是完整链接，返回链接
            return $path;
        }
        $x = $x ?: 0;
        $y = $y ?: 0;
        if ($x == 0 && $y == 0) {
            // 如果没有裁剪，返回原图
            return oss($path);
        }
        switch ($_L['plugin']['oss']['type']) {
            case 'qiniu':
                $url = oss($path);
                if (is_url($url)) {
                    return "{$url}?imageMogr2/auto-orient/thumbnail/!{$x}x{$y}r/gravity/Center/crop/{$x}x{$y}/blur/1x0/quality/75";
                }
                break;
            case 'tencent':
                $url = oss($path);
                if (is_url($url)) {
                    return "{$url}?imageMogr2/thumbnail/!{$x}x{$y}r/|imageMogr2/gravity/center/crop/{$x}x{$y}/interlace/0";
                }
                break;
        }
        $mime = strrchr($path, ".");
        $mime = $mime ? $mime : ".jpg";
        $para = base64_encode($path . "|" . $x . "|" . $y);
        if ($html) {
            $site = $_L['url']['web']['site'] ?: $_L['url']['site'];
            $url  = "{$site}images/{$para}{$mime}";
        } else {
            $site = $_L['url']['web']['own'] ?: $_L['url']['own'];
            $url  = "{$site}n=system&c=cut&para={$para}";
        }
        return $url;
    }
    public static function create($path, $x = "", $y = "")
    {
        ob_end_clean();
        $img_info = @getimagesize($path);
        if (stripos($img_info['mime'], "image/jpeg|image/pjpeg|image/gif|image/png|image/x-png") != false) {
            header("content-type: {$img_info['mime']}");
            echo file_get_contents($path);
        }
        $img   = self::img_resource($path, $img_info[2]);
        $scale = $img_info[0] / $img_info[1];
        $x     = $x == 0 ? $y * $scale : ($x > 1920 ? 1920 : $x);
        $y     = $y == 0 ? $x / $scale : ($y > 1000 ? 1000 : $y);
        $thumb = imagecreatetruecolor($x, $y);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagefilledrectangle($thumb, 0, 0, $x, $y, imagecolorallocate($thumb, 255, 255, 255));
        if ($img_info[0] / $x > $img_info[1] / $y) {
            $w = $img_info[1] * ($x / $y);
            $h = $img_info[1];
        } else {
            $w = $img_info[0];
            $h = $img_info[0] / ($x / $y);
        }
        $scr_x = ($img_info[0] - $w) / 2;
        $scr_y = ($img_info[1] - $h) / 2;
        imagecopyresampled($thumb, $img, 0, 0, $scr_x, $scr_y, $x, $y, $w, $h);
        ob_end_clean();
        header("content-type: {$img_info['mime']}");
        switch ($img_info['mime']) {
            case 'image/gif':
                imagegif($thumb);
                break;
            case 'image/pjpeg':
            case 'image/jpeg':
                imagejpeg($thumb, null, 100);
                break;
            case 'image/x-png':
            case 'image/png':
                imagepng($thumb);
                break;
        }
        imagedestroy($thumb);
        imagedestroy($img);
    }
    protected static function img_resource($img, $mime_type)
    {
        switch ($mime_type) {
            case 1:
            case 'image/gif':
                $res = imagecreatefromgif($img);
                break;
            case 2:
            case 'image/pjpeg':
            case 'image/jpeg':
                $res = imagecreatefromjpeg($img);
                break;
            case 3:
            case 'image/x-png':
            case 'image/png':
                $res = imagecreatefrompng($img);
                break;
            default:
                return false;
        }
        return $res;
    }
}
