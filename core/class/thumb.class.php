<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-08-30 23:02:16
 * @Description:缩略图生成类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class THUMB
{
    /**
     * @description:
     * @param string $path
     * @param int $x
     * @param int $y
     * @param bool $rewrite
     * @param bool $watermark
     * @return {*}
     */
    public static function url($path = "", $x = 0, $y = 0, $rewrite = false, $watermark = true)
    {
        global $_L;
        $x      = $x ?: 0;
        $y      = $y ?: 0;
        $cfgoss = $_L['plugin']['oss'] ?: [];
        // 如果是完整链接，返回原图
        if (is_url($path) && (!$cfgoss['domain'] || !in_string($path, $cfgoss['domain']))) {
            return $path;
        }
        //如果没有裁剪，返回原图
        if ($x == 0 && $y == 0) {
            return oss($path, $watermark);
        }
        //如果使用云存储
        if ($cfgoss['type'] != "local") {
            $url = oss($path, $watermark);
            if (is_url($url)) {
                switch ($cfgoss['type']) {
                    case 'qiniu':
                        $url = explode("|", $url);
                        if ($x != 0 && $y == 0) {
                            $url[0] .= "/auto-orient/thumbnail/{$x}x/blur/1x0";
                        } elseif ($x == 0 && $y != 0) {
                            $url[0] .= "/auto-orient/thumbnail/x{$y}/blur/1x0";
                        } elseif ($x && $y) {
                            $url[0] .= "/auto-orient/thumbnail/!{$x}x{$y}r/gravity/Center/crop/{$x}x{$y}/blur/1x0";
                        }
                        $url = $url[1] ? implode("|", $url) : $url[0];
                        break;
                    case 'tencent':
                        $url = explode("|", $url);
                        if ($x != 0 && $y == 0) {
                            $url[0] .= "/thumbnail/{$x}x/gravity/center/crop/{$x}x0";
                        } elseif ($x == 0 && $y != 0) {
                            $url[0] .= "/thumbnail/x{$y}/gravity/center/crop/0x{$y}";
                        } elseif ($x && $y) {
                            $url[0] .= "/thumbnail/!{$x}x{$y}r/gravity/center/crop/{$x}x{$y}";
                        }
                        $url = $url[1] ? implode("|", $url) : $url[0];
                        break;
                    case 'aliyun':
                        $url = explode("q_75", $url);
                        if ($x != 0 && $y == 0) {
                            $url[0] .= "/resize,m_lfit,w_{$x},limit_0";
                        } elseif ($x == 0 && $y != 0) {
                            $url[0] .= "/resize,m_lfit,h_{$y},limit_0";
                        } elseif ($x && $y) {
                            $url[0] .= "/resize,m_fill,w_{$x},h_{$y},limit_0";
                        }
                        $url = $url[1] ? implode("q_75", $url) : $url[0];
                        break;
                }
                return $url;
            }
        }
        if ($path && in_string($path, "../")) {
            //本地存储处理
            $path = str_replace("../", "", $path);
            $urlw = $_L['url']['web'];
            $para = "{$x},{$y},{$path}";
            if ($rewrite) {
                $site = $urlw['site'] ?: $_L['url']['site'];
                $url  = "{$site}images/{$para}";
            } else {
                $site = $urlw['own'] ?: $_L['url']['own'];
                $url  = "{$site}n=system&c=cut&para={$para}";
            }
        }
        return $url;
    }
    /**
     * @description:
     * @param string $path
     * @param int $x
     * @param int $y
     * @return {*}
     */
    public static function create($path, $x = 0, $y = 0)
    {
        global $_L;
        ob_clean();
        $img_info = getimagesize($path);
        $img_data = file_get_contents($path);
        if (!$img_info || !in_array($img_info['mime'], [
            "image/jpeg",
            "image/pjpeg",
            "image/gif",
            "image/png",
            "image/x-png",
            "image/webp",
            "image/vnd.wap.wbmp",
            "image/x-up-wpng",
        ])) {
            header("content-type: {$img_info['mime']}");
            echo $img_data;
            exit;
        }
        $img   = imagecreatefromstring($img_data);
        $scale = $img_info[0] / $img_info[1];
        if ($x == 0 && $y == 0) {
            $x = $img_info[0];
            $y = $img_info[1];
        } else {
            $x = $x == 0 ? $y * $scale : ($x > 1920 ? 1920 : $x);
            $y = $y == 0 ? $x / $scale : ($y > 1000 ? 1000 : $y);
        }
        $thumb = imagecreatetruecolor($x, $y);
        imagealphablending($thumb, true);
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
        header("content-type: {$img_info['mime']}");
        switch ($img_info['mime']) {
            case 'image/gif':
                imagegif($thumb);
                break;
            case 'image/pjpeg':
            case 'image/jpeg':
                imagejpeg($thumb, null, 100);
                break;
            case 'image/x-up-wpng':
            case 'image/x-png':
            case 'image/png':
                imagepng($thumb);
                break;
            case 'image/webp':
                imagewebp($thumb);
                break;
            case 'image/vnd.wap.wbmp':
                imagewbmp($thumb);
                break;
        }
        imagedestroy($thumb);
        imagedestroy($img);
    }
}
