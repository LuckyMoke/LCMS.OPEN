<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-05-20 09:50:03
 * @Description:文件上传类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class UPLOAD
{
    private static $CFG;
    private static $ONAME;
    private static $MIME;
    private static $SIZE;
    /**
     * @description: 文件上传操作
     * @param string $dir
     * @param array|string $form
     * @param string $mime
     * @param bool $force
     * @return array
     */
    public static function file($dir, $form = "", $mime = "", $force = 0)
    {
        global $_L;
        self::$CFG = $_L['config']['admin'];
        self::$CFG = array_merge(self::$CFG, [
            "attsize"      => intval((self::$CFG['attsize'] ?: 300) * 1024),
            "attsize_file" => intval((self::$CFG['attsize_file'] ?: 300) * 1024),
        ]);
        if ($dir == "image" || $dir == "file" || $dir == "user") {
            $dir = PATH_UPLOAD . "{$_L['ROOTID']}/{$dir}/" . date("Ym") . "/";
        }
        if (makedir($dir)) {
            if (!is_array($form) && is_url($form)) {
                $result = HTTP::get($form, true);
                if ($result['code'] == 200 && $result['length'] > 0) {
                    $file       = $result['body'];
                    self::$MIME = $mime ?: self::mime($result['type']);
                    self::$SIZE = $result['length'];
                    $file       = self::img2watermark($file);
                } else {
                    return self::out(0, "远程文件下载失败");
                }
            } else {
                // 如果文件地址是本地上传
                $file = $form ?: $_FILES['file'];
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
                self::$MIME  = strtolower(substr($file['name'], strrpos($file['name'], ".") + 1));
                self::$SIZE  = $file['size'];
                self::$ONAME = $file['name'];
                $file        = self::img2watermark(file_get_contents($file['tmp_name']));
            }
            if (in_array(self::$MIME, [
                "jpeg", "jpg", "png", "bmp", "webp", "wpng", "wbmp",
            ])) {
                if (self::$SIZE > self::$CFG['attsize']) {
                    // 如果图片大小超过上传限制
                    return self::out(0, "图片大小超过" . intval(self::$CFG['attsize'] / 1024) . "KB");
                }
            } elseif (self::$SIZE > self::$CFG['attsize_file']) {
                // 如果文件大小超过上传限制
                return self::out(0, "文件大小超过" . intval(self::$CFG['attsize_file'] / 1024) . "KB");
            }
            if ($_L['ROOTID'] > 0) {
                $admin = sql_get([
                    "table" => "admin",
                    "where" => "id = {$_L['ROOTID']}",
                ]);
                if ($admin['storage'] > 0 && self::$SIZE > intval(($admin['storage'] - $admin['storage_used']) * 1024)) {
                    //如果上传文件超过存储空间限制
                    return self::out(0, "存储空间已满");
                }
            }
            if (self::$MIME && in_array(self::$MIME, explode("|", self::$CFG['mimelist']))) {
                $name = date("dHis") . randstr(6) . "." . self::$MIME;
                if (file_put_contents("{$dir}{$name}", $file)) {
                    $return = self::out(1, "上传成功", path_relative($dir, "../"), $name, self::$SIZE);
                } else {
                    return self::out(0, "上传失败");
                }
            } else {
                return self::out(0, "禁止上传此格式文件");
            }
        } else {
            return self::out(0, "upload文件夹没有写权限");
        }
        //强制本地存储
        if ($force) {
            self::sql_save($dir, $return, true);
            return $return;
        }
        //云存储处理
        $osscfg = $_L['plugin']['oss'];
        if ($return['src']) {
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
                case 'baidu':
                    load::plugin("Baidu/BaiduOSS");
                    $OSS = new BaiduOSS($osscfg['baidu']);
                    $rst = $OSS->upload($return['src']);
                    break;
            }
            if ($rst) {
                delfile($return['src']);
                if ($rst['code'] == 1) {
                    $return['url'] = $osscfg['domain'] . str_replace("../", "", $return['src']);
                } else {
                    return self::out(0, "云存储上传失败");
                }
            }
        }
        self::sql_save($dir, $return);
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
            "original" => "{$dir}{$filename}",
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
    private static function img2watermark($img, $times = 1)
    {
        global $_L;
        $cfgwat = $_L['plugin']['watermark'] ?: [];
        if (self::$CFG['attwebp'] > 0 && in_array(self::$MIME, [
            "jpeg", "jpg", "png", "bmp", "wpng", "wbmp",
        ]) && function_exists("imagewebp")) {
            self::$MIME = "webp";
        }
        if (in_array(self::$MIME, [
            "jpeg", "jpg", "png", "bmp", "webp", "wpng", "wbmp",
        ]) && (
            self::$SIZE > self::$CFG['attsize'] ||
            $cfgwat['on'] > 0
        )) {
            ob_start();
            $src    = imagecreatefromstring($img);
            $srcwh  = getimagesizefromstring($img);
            $thumbx = $srcwh[0];
            $thumby = $srcwh[1];
            if ($thumbx > 2560 || $thumby > 2560) {
                if ($thumbx > 2560) {
                    $thumby = intval($thumby * 2560 / $thumbx);
                    $thumbx = 2560;
                } else {
                    $thumbx = intval($thumbx * 2560 / $thumby);
                    $thumby = 2560;
                }
            }
            if (self::$SIZE > self::$CFG['attsize']) {
                //如果图片大小超过，启用压缩
                $thumbx = intval($thumbx * $times);
                $thumby = intval($thumby * $times);
                $thumb  = imagecreatetruecolor($thumbx, $thumby);
                imagealphablending($thumb, true);
                imagesavealpha($thumb, true);
                if (in_array(self::$MIME, ["jpeg", "jpg", "bmp", "wbmp"])) {
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
                    $thumb = $src;
                    imagealphablending($thumb, true);
                    imagesavealpha($thumb, true);
                }
                self::watermark($thumb, $thumbx, $thumby);
            }
            imageinterlace($thumb, true);
            switch (self::$MIME) {
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
            $nimg = ob_get_contents();
            imagedestroy($src);
            ob_clean();
            self::$SIZE = strlen($nimg);
            if ($cfgwat['on'] > 0) {
                self::$SIZE = self::$SIZE - 5000;
            }
            $times = $times - 0.1;
            if (self::$SIZE > self::$CFG['attsize'] && $times > 0) {
                $nimg = self::img2watermark($img, $times);
            }
            $img = $nimg;
        }
        return $img;
    }
    private static function watermark($image, $w, $h)
    {
        global $_L;
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
    /**
     * @description: 数据库操作
     * @param string $dir
     * @param array $data
     * @return {*}
     */
    private static function sql_save($dir, $data = [], $force = false)
    {
        global $_L;
        if ($data['code'] == 1) {
            $info = path_relative($dir);
            $info = explode("/", $info);
            if ($info[2] == "file" || $info[2] == "image" && strlen($info[3]) == 6) {
                sql_insert(["upload", [
                    "type"    => $info[2],
                    "datey"   => $info[3],
                    "oname"   => self::$ONAME ?: NULL,
                    "name"    => $data['filename'],
                    "size"    => $data['size'],
                    "src"     => $data['src'],
                    "local"   => $force ? 1 : 0,
                    "addtime" => datenow(),
                    "uid"     => $_L['LCMSADMIN']['id'] ?: ($_L['ROOTID'] > 0 ?: 1),
                    "lcms"    => $_L['ROOTID'],
                ]]);
                $_L['ROOTID'] > 0 && sql_update([
                    "table" => "admin",
                    "data"  => [
                        "storage_used" => intval($data['size'] / 1024),
                    ],
                    "where" => "id = {$_L['ROOTID']}",
                    "math"  => [
                        "storage_used" => "+",
                    ],
                ]);
            }
        }
    }
}
