<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-07-14 14:15:04
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
        //裁剪计算
        $x = $x ? ($x == "auto" ? 0 : $x) : 0;
        $y = $y ? ($y == "auto" ? 0 : $y) : 0;
        //云存储配置
        $cfgoss   = $_L['plugin']['oss'] ?: [];
        $cfgthumb = $_L['plugin']['thumb'] ?: [];
        //如果是完整链接，返回原图
        if (is_url($path) && (!$cfgoss['domain'] || !in_string($path, $cfgoss['domain']))) {
            return $path;
        }
        //如果没有裁剪，返回原图
        if ($x == 0 && $y == 0) {
            return oss($path, $watermark);
        }
        //如果格式不匹配，返回原图
        if (!in_string($path, [
            "jpg", "jpeg", "png", "gif", "bmp", "webp", "wpng", "wbmp",
        ])) {
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
                            if ($cfgthumb['type'] > 0) {
                                $url[0] .= "/auto-orient/thumbnail/{$x}x{$y}/gravity/Center/blur/1x0/background/I0ZGRkZGRg==/extent/!{$x}x{$y}";
                            } else {
                                $url[0] .= "/auto-orient/thumbnail/!{$x}x{$y}r/gravity/Center/crop/{$x}x{$y}/blur/1x0";
                            }
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
                            if ($cfgthumb['type'] > 0) {
                                $url[0] .= "/thumbnail/{$x}x{$y}!/gravity/center/crop/{$x}x{$y}/pad/1/color/I0ZGRkZGRg";
                            } else {
                                $url[0] .= "/thumbnail/{$x}x{$y}!/gravity/center/crop/{$x}x{$y}";
                            }
                        }
                        $url = $url[1] ? implode("|", $url) : $url[0];
                        break;
                    case 'aliyun':
                        $url = explode("/quality,q_75", $url);
                        if ($x != 0 && $y == 0) {
                            $url[0] .= "/resize,m_lfit,w_{$x},limit_0";
                        } elseif ($x == 0 && $y != 0) {
                            $url[0] .= "/resize,m_lfit,h_{$y},limit_0";
                        } elseif ($x && $y) {
                            if ($cfgthumb['type'] > 0) {
                                $url[0] .= "/resize,m_pad,w_{$x},h_{$y},limit_0,color_FFFFFF";
                            } else {
                                $url[0] .= "/resize,m_fill,w_{$x},h_{$y},limit_0";
                            }
                        }
                        $url = $url[1] ? implode("/quality,q_75", $url) : $url[0];
                        break;
                    case 'baidu':
                        $url = explode("/quality,q_75", $url);
                        if ($x != 0 && $y == 0) {
                            $url[0] .= "/resize,m_lfit,w_{$x},limit_0";
                        } elseif ($x == 0 && $y != 0) {
                            $url[0] .= "/resize,m_lfit,h_{$y},limit_0";
                        } elseif ($x && $y) {
                            if ($cfgthumb['type'] > 0) {
                                $url[0] .= "/resize,m_pad,w_{$x},h_{$y},limit_0,color_FFFFFF";
                            } else {
                                $url[0] .= "/resize,m_fill,w_{$x},h_{$y},limit_0";
                            }
                        }
                        $url = $url[1] ? implode("/quality,q_75", $url) : $url[0];
                        break;
                }
                return $url;
            }
        }
        if ($path && in_string($path, "../")) {
            //本地存储处理
            $path  = str_replace("../", "", $path);
            $style = "{$x},{$y},{$path}";
            if ($rewrite) {
                $url = "{$_L['url']['site']}images/{$style}";
            } else {
                $url = "{$_L['url']['app']}index.php?n=system&c=cut&style={$style}";
            }
        }
        return $url ?: $path;
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
        $cfgthumb = $_L['plugin']['thumb'] ?: [];
        $img_info = getimagesize($path);
        $img_data = file_get_contents($path);
        ob_clean();
        if (!$img_info || !in_array($img_info['mime'], [
            "image/jpeg",
            "image/pjpeg",
            "image/gif",
            "image/png",
            "image/x-png",
            "image/webp",
            "image/vnd.wap.wbmp",
            "image/x-up-wpng",
        ]) || ($x == 0 && $y == 0)) {
            header("content-type: {$img_info['mime']}");
            echo $img_data;
            exit;
        }
        $img   = imagecreatefromstring($img_data);
        $scale = $img_info[0] / $img_info[1];
        $x     = $x == 0 ? $y * $scale : ($x > 1920 ? 1920 : $x);
        $y     = $y == 0 ? $x / $scale : ($y > 1000 ? 1000 : $y);
        $thumb = imagecreatetruecolor($x, $y);
        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);
        if (in_string($img_info['mime'], ["jpeg", "bmp"])) {
            $bgcolor = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        } else {
            $bgcolor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        }
        imagefill($thumb, 0, 0, $bgcolor);
        $dstx = $dsty = $srcx = $srcy = 0;
        if ($img_info[0] / $img_info[1] > $x / $y) {
            if ($cfgthumb['type'] > 0) {
                $dsty = (int) ($y - $img_info[1] * $x / $img_info[0]) / 2;
                $y    = $y - 2 * $dsty;
            } else {
                $srcx        = (int) ($img_info[0] - $x * $img_info[1] / $y) / 2;
                $img_info[0] = $img_info[0] - 2 * $srcx;
            }
        } else {
            if ($cfgthumb['type'] > 0) {
                $dstx = (int) ($x - $img_info[0] * $y / $img_info[1]) / 2;
                $x    = $x - 2 * $dstx;
            } else {
                $srcy        = (int) ($img_info[1] - $y * $img_info[0] / $x) / 2;
                $img_info[1] = $img_info[1] - 2 * $srcy;
            }
        }
        imagecopyresampled($thumb, $img, $dstx, $dsty, $srcx, $srcy, $x, $y, $img_info[0], $img_info[1]);
        if (
            in_string($_SERVER['HTTP_ACCEPT'], "image/webp") &&
            $_L['config']['admin']['thumbwebp'] != 1
        ) {
            $img_info['mime'] = "image/webp";
        }
        header("content-type: {$img_info['mime']}");
        imageinterlace($thumb, true);
        switch ($img_info['mime']) {
            case 'image/gif':
                imagegif($thumb);
                break;
            case 'image/pjpeg':
            case 'image/jpeg':
                imagejpeg($thumb, null, 90);
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
