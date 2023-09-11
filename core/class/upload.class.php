<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-09-10 15:57:00
 * @Description:文件上传类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class UPLOAD
{
    /**
     * @description: 文件上传操作
     * @param string $dir
     * @param array|string $para
     * @param string $mime
     * @param bool $force
     * @return array
     */
    public static function file($dir, $para = "", $mime = "", $force = 0)
    {
        global $_L, $CFG, $MIME, $SIZE;
        $CFG                 = $_L['config']['admin'];
        $CFG['attsize']      = $CFG['attsize'] ?: 300;
        $CFG['attsize_file'] = $CFG['attsize_file'] ?: 300;
        if (makedir($dir)) {
            if (!is_array($para) && is_url($para)) {
                $result = HTTP::get($para, true);
                if ($result['code'] == 200 && $result['length'] > 0) {
                    $file = $result['body'];
                    $MIME = $mime ?: self::mime($result['type']);
                    $SIZE = $result['length'];
                    $file = self::img2watermark($file);
                } else {
                    self::out(0, "远程文件下载失败");
                }
            } else {
                // 如果文件地址是本地上传
                $file = $para ?: $_FILES['file'];
                if ($file['error'] != 0) {
                    switch ($file['error']) {
                        case 1:
                            $file['error'] = "上传文件大小超过php.ini限制";
                            break;
                        default:
                            $file['error'] = "错误代码/{$file['error']}";
                            break;
                    }
                    return self::out(0, "上传失败:{$file['error']}");
                }
                $MIME = strtolower(substr($file['name'], strrpos($file['name'], ".") + 1));
                $SIZE = $file['size'];
                $file = self::img2watermark(file_get_contents($file['tmp_name']));
            }
            if (in_array($MIME, [
                "jpeg", "jpg", "png", "bmp", "webp", "wpng", "wbmp",
            ])) {
                if (round($SIZE / 1024) > $CFG['attsize']) {
                    // 如果图片大小超过上传限制
                    return self::out(0, "图片大小超过{$CFG['attsize']}KB");
                }
            } elseif (round($SIZE / 1024) > $CFG['attsize_file']) {
                // 如果文件大小超过上传限制
                return self::out(0, "文件大小超过{$CFG['attsize_file']}KB");
            }
            if ($MIME && in_array($MIME, explode("|", $CFG['mimelist']))) {
                $name = date("dHis") . randstr(6) . ".{$MIME}";
                if (file_put_contents("{$dir}{$name}", $file)) {
                    $return = self::out(1, "上传成功", path_relative($dir, "../"), $name, $SIZE);
                } else {
                    $return = self::out(0, "上传失败");
                }
            } else {
                $return = self::out(0, "禁止上传此格式文件");
            }
        } else {
            return self::out(0, "upload文件夹没有写权限");
        }
        //强制本地存储
        if ($force) {
            return $return;
        }
        //云存储处理
        $osscfg = $_L['plugin']['oss'];
        if ($return['code'] == 1) {
            switch ($osscfg['type']) {
                case 'qiniu':
                    load::plugin("Qiniu/QiniuOSS");
                    $OSS = new QiniuOSS($osscfg['qiniu']);
                    $rst = $OSS->upload($return['src']);
                    break;
                case 'tencent':
                    load::plugin("Tencent/TencentOSS");
                    $OSS = new TencentOSS($osscfg['tencent']);
                    $rst = $OSS->upload($return['src']);
                    break;
                case 'aliyun':
                    load::plugin("Aliyun/AliyunOSS");
                    $OSS = new AliyunOSS($osscfg['aliyun']);
                    $rst = $OSS->upload($return['src']);
                    break;
            }
            if ($rst['code'] == 1) {
                $return['url'] = $osscfg['domain'] . str_replace("../", "", $return['src']);
                delfile($return['src']);
            }
        }
        return $return;
    }
    /**
     * @description: 上传结果输出
     * @param int $code
     * @param string $msg
     * @param string $dir
     * @param string $filename
     * @param int $size
     * @return array
     */
    public static function out($code, $msg, $dir = "", $filename = "", $size = 0)
    {
        return [
            "code"     => $code,
            "msg"      => $msg,
            "dir"      => $dir,
            "filename" => $filename,
            "src"      => "{$dir}{$filename}",
            "datasrc"  => "{$dir}{$filename}",
            "size"     => $size,
        ];
    }
    /**
     * @description: 获取文件格式和mime对应关系
     * @param string $mime
     * @return string
     */
    public static function mime($mime = "")
    {
        $allmime = [
            "image/jpeg"          => "jpeg",
            "image/png"           => "png",
            "image/bmp"           => "bmp",
            "image/gif"           => "gif",
            "image/webp"          => "webp",
            "image/vnd.wap.wbmp"  => "wbmp",
            "image/x-up-wpng"     => "wpng",
            "image/x-icon"        => "ico",
            "image/svg+xml"       => "svg",
            "image/tiff"          => "tiff",
            "audio/mpeg"          => "mp3",
            "audio/ogg"           => "ogg",
            "audio/x-wav"         => "wav",
            "audio/x-ms-wma"      => "wma",
            "audio/x-ms-wmv"      => "wmv",
            "video/mp4"           => "mp4",
            "video/mpeg"          => "mpeg",
            "video/quicktime"     => "mov",
            "application/json"    => "json",
            "application/pdf"     => "pdf",
            "binary/octet-stream" => "jpg",
        ];
        return $allmime[$mime] ?: "";
    }
    /**
     * @description: 图片转webp或加水印
     * @param string $img
     * @return string
     */
    private static function img2watermark($img)
    {
        global $_L, $CFG, $MIME, $SIZE;
        $cfgwat = $_L['plugin']['watermark'] ?: [];
        if ($CFG['attwebp'] > 0 && in_array($MIME, [
            "jpeg", "jpg", "png", "bmp", "wpng", "wbmp",
        ]) && function_exists("imagewebp")) {
            $MIME = "webp";
        }
        if (in_array($MIME, [
            "jpeg", "jpg", "png", "bmp", "webp", "wpng", "wbmp",
        ]) && (
            round($SIZE / 1024) > $CFG['attsize'] ||
            $cfgwat['on'] > 0
        )) {
            ob_start();
            $src   = imagecreatefromstring($img);
            $srcwh = getimagesizefromstring($img);
            if (round($SIZE / 1024) > $CFG['attsize']) {
                //如果图片大小超过，启用压缩
                $times  = $CFG['attsize'] / round($SIZE / 1024);
                $thumbx = intval($srcwh[0] * $times);
                $thumby = intval($srcwh[1] * $times);
                if ($thumbx > 1920 || $thumby > 1920) {
                    if ($thumbx > 1920) {
                        $thumbx = 1920;
                        $thumby = intval($thumby * 1920 / $srcwh[0]);
                    } else {
                        $thumbx = intval($thumbx * 1920 / $srcwh[1]);
                        $thumby = 1920;
                    }
                }
                $thumb = imagecreatetruecolor($thumbx, $thumby);
                imagealphablending($thumb, true);
                imagesavealpha($thumb, true);
                if (in_array($MIME, ["jpeg", "jpg", "bmp", "wbmp"])) {
                    $bgcolor = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                } else {
                    $bgcolor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                }
                imagefill($thumb, 0, 0, $bgcolor);
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbx, $thumby, $srcwh[0], $srcwh[1]);
            }
            if ($cfgwat['on'] > 0) {
                //如果要加水印
                if (!$thumb) {
                    $thumb  = $src;
                    $thumbx = $srcwh[0];
                    $thumby = $srcwh[1];
                    imagealphablending($thumb, true);
                    imagesavealpha($thumb, true);
                }
                self::watermark($thumb, $thumbx, $thumby);
            }
            switch ($MIME) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumb);
                    break;
                case 'png':
                    imagepng($thumb);
                    break;
                case 'bmp':
                    imagebmp($thumb);
                    break;
                case 'webp':
                case 'wpng':
                    imagewebp($thumb);
                    break;
                case 'wbmp':
                    imagewbmp($thumb);
                    break;
            }
            imagedestroy($thumb);
            $img = ob_get_contents();
            imagedestroy($src);
            ob_clean();
            $SIZE = strlen($img);
            if ($cfgwat['on'] > 0) {
                $SIZE = $SIZE - 5000;
            }
        }
        return $img;
    }
    private static function watermark($image, $w, $h)
    {
        global $_L, $CFG, $MIME, $SIZE;
        $cfgwat = $_L['plugin']['watermark'] ?: [];
        $font   = PATH_PUBLIC . "static/fonts/Chinese.ttf";
        $text   = self::imagettfbboxextended($cfgwat['size'], 0, $font, $cfgwat['text']);
        if ($text['width'] <= $w) {
            switch ($cfgwat['gravity']) {
                case 'NorthWest':
                    $x = 0 + $cfgwat['dx'];
                    $y = $text['height'] + $cfgwat['dy'];
                    break;
                case 'North':
                    $x = ($w / 2) - ($text['width'] / 2) + $cfgwat['dx'];
                    $y = $text['height'] + $cfgwat['dy'];
                    break;
                case 'NorthEast':
                    $x = $w - $text['width'] - $cfgwat['dx'];
                    $y = $text['height'] + $cfgwat['dy'];
                    break;
                case 'West':
                    $x = 0 + $cfgwat['dx'];
                    $y = ($h / 2) + ($text['height'] / 2) + $cfgwat['dy'];
                    break;
                case 'Center':
                    $x = ($w / 2) - ($text['width'] / 2) + $cfgwat['dx'];
                    $y = ($h / 2) + ($text['height'] / 2) + $cfgwat['dy'];
                    break;
                case 'East':
                    $x = $w - $text['width'] - $cfgwat['dx'];
                    $y = ($h / 2) + ($text['height'] / 2) + $cfgwat['dy'];
                    break;
                case 'SouthWest':
                    $x = 0 + $cfgwat['dx'];
                    $y = $h - $cfgwat['dy'];
                    break;
                case 'South':
                    $x = ($w / 2) - ($text['width'] / 2) + $cfgwat['dx'];
                    $y = $h - $cfgwat['dy'];
                    break;
                case 'SouthEast':
                    $x = $w - $text['width'] - $cfgwat['dx'];
                    $y = $h - $cfgwat['dy'];
                    break;
            }
            imagettftext($image, $cfgwat['size'], 0, $x + 1, $y + 1, imagecolorallocatealpha($image, 0, 0, 0, (100 - $cfgwat['shadow']) / 100 * 127), $font, $cfgwat['text']);
            list($r, $g, $b) = sscanf($cfgwat['fill'], "#%02x%02x%02x");
            imagettftext($image, $cfgwat['size'], 0, $x, $y, imagecolorallocatealpha($image, $r, $g, $b, (100 - $cfgwat['dissolve']) / 100 * 127), $font, $cfgwat['text']);
        }
    }
    private static function imagettfbboxextended($size, $angle, $fontfile, $text)
    {
        $bbox = imagettfbbox($size, $angle, $fontfile, $text);
        if ($bbox[0] >= -1) {
            $bbox['x'] = abs($bbox[0] + 1) * -1;
        } else {
            $bbox['x'] = abs($bbox[0] + 2);
        }
        $bbox['width'] = abs($bbox[2] - $bbox[0]);
        if ($bbox[0] < -1) {
            $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
        }
        $bbox['y']      = abs($bbox[5] + 1);
        $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
        if ($bbox[3] > 0) {
            $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
        }
        return $bbox;
    }
}
