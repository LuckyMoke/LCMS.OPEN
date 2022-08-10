<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-08-09 17:11:42
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
     * @return array
     */
    public static function file($dir, $para = "", $mime = "")
    {
        global $_L, $CFG, $MIME, $SIZE;
        $CFG = $_L['config']['admin'];
        if (makedir($dir)) {
            if (!is_array($para) && is_url($para)) {
                // 如果文件地址是url链接，远程下载
                $result = HTTP::get($para, true);
                if ($result['code'] == 200 && $result['length'] > 0) {
                    $file = $result['body'];
                    $MIME = $mime ?: self::mime($result['type']);
                    $SIZE = $result['length'];
                    $file = self::img2webp($file);
                } else {
                    self::out(0, "远程文件下载失败");
                }
            } else {
                // 如果文件地址是本地上传
                $file = $para ?: $_FILES['file'];
                if ($file['error'] != 0) {
                    return self::out(0, "上传失败 CODE:{$file['error']}");
                }
                $MIME = substr($file['name'], strrpos($file['name'], ".") + 1);
                $SIZE = $file['size'];
                $file = self::img2webp(file_get_contents($file['tmp_name']));
            }

            if (round($SIZE / 1024) > $CFG['attsize']) {
                // 如果文件大小超过上传限制
                $return = self::out(0, "文件大小超过{$CFG['attsize']}KB");
            } else {
                if ($MIME && in_array($MIME, explode("|", $CFG['mimelist']))) {
                    $name = date("dHis") . randstr(6) . ".{$MIME}";
                    if (file_put_contents("{$dir}{$name}", $file)) {
                        $return = self::out(1, "上传成功", path_relative($dir), $name, $SIZE);
                    } else {
                        $return = self::out(0, "上传失败");
                    }
                } else {
                    $return = self::out(0, "禁止上传此格式文件");
                }
            }
        } else {
            $return = self::out(0, "upload文件夹没有写权限");
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
            "image/jpeg"         => "jpeg",
            "image/png"          => "png",
            "image/bmp"          => "bmp",
            "image/gif"          => "gif",
            "image/webp"         => "webp",
            "image/vnd.wap.wbmp" => "wbmp",
            "image/x-up-wpng"    => "wpng",
            "image/x-icon"       => "ico",
            "image/svg+xml"      => "svg",
            "image/tiff"         => "tiff",
            "audio/mpeg"         => "mp3",
            "audio/ogg"          => "ogg",
            "audio/x-wav"        => "wav",
            "audio/x-ms-wma"     => "wma",
            "audio/x-ms-wmv"     => "wmv",
            "video/mp4"          => "mp4",
            "video/mpeg"         => "mpeg",
            "video/quicktime"    => "mov",
            "application/json"   => "json",
            "application/pdf"    => "pdf",
        ];
        return $allmime[$mime] ?: "";
    }
    /**
     * @description: 图片转webp
     * @param string $img
     * @return string
     */
    private static function img2webp($img)
    {
        global $_L, $CFG, $MIME, $SIZE;
        if ($CFG['attwebp'] > 0 && in_array($MIME, [
            "jpeg", "jpg", "png",
        ]) && function_exists("imagewebp")) {
            $res = imagecreatefromstring($img);
            imagewebp($res);
            imagedestroy($res);
            $img = ob_get_contents();
            ob_end_clean();
            $MIME = "webp";
            $SIZE = strlen($img);
        }
        return $img;
    }
}
