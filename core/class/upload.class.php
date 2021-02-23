<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-02-23 12:04:33
 * @Description:文件上传类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class UPLOAD
{
    /**
     * @文件上传:
     * @param {*}
     * @return {*}
     */
    public static function file($dir, $para = "", $mime = "")
    {
        global $_L;
        if (makedir($dir)) {
            if (is_url($para)) {
                // 如果文件地址是url链接，远程下载
                $result = HTTP::get($para, true);
                if ($result['code'] == 200 && $result['length'] > 0) {
                    $file = $result['body'];
                    $mime = $mime ? $mime : self::mime($result['type']);
                    $size = round($result['length'] / 1024);
                } else {
                    self::out(0, "远程文件下载失败");
                }
            } else {
                // 如果文件地址是本地上传
                $file = $para ? $para : $_FILES['file'];
                if ($file['error'] != 0) {
                    return self::out(0, "上传失败 CODE:{$file['error']}");
                }
                $mime = substr($file['name'], strrpos($file['name'], ".") + 1);
                $size = round($file['size'] / 1024);
            }
            if ($size > $_L['config']['admin']['attsize']) {
                // 如果文件大小超过上传限制
                $return = self::out(0, "超过上传大小限制");
            } else {
                if (stripos($_L['config']['admin']['mimelist'], $mime) !== false) {
                    $name = strval(time()) . microseconds() . ".{$mime}";
                    if (is_url($para) && file_put_contents("{$dir}{$name}", $file)) {
                        $return = self::out(1, "上传成功", path_relative($dir), $name);
                    } elseif (move_uploaded_file($file['tmp_name'], "{$dir}{$name}")) {
                        $return = self::out(1, "上传成功", path_relative($dir), $name);
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
     * @上传结果输出:
     * @param {*}
     * @return {*}
     */
    public static function out($code, $msg, $dir = "", $filename = "")
    {
        return [
            "code"     => $code,
            "msg"      => $msg,
            "dir"      => $dir,
            "filename" => $filename,
        ];
    }
    /**
     * @获取文件格式和mime对应关系:
     * @param {*}
     * @return {*}
     */
    public static function mime($mime = "")
    {
        $allmime = [
            "image/jpeg"                   => "jpeg",
            "image/png"                    => "png",
            "image/bmp"                    => "bmp",
            "image/gif"                    => "gif",
            "image/vnd.wap.wbmp"           => "wbmp",
            "image/x-up-wpng"              => "wpng",
            "image/x-icon"                 => "ico",
            "image/svg+xml"                => "svg",
            "image/tiff"                   => "tiff",
            "audio/mpeg"                   => "mp3",
            "audio/ogg"                    => "ogg",
            "audio/x-wav"                  => "wav",
            "audio/x-ms-wma"               => "wma",
            "audio/x-ms-wmv"               => "wmv",
            "video/mp4"                    => "mp4",
            "video/mpeg"                   => "mpeg",
            "video/quicktime"              => "mov",
            "flv-application/octet-stream" => "flv",
            "application/json"             => "json",
            "application/octet-stream"     => "rar",
            "application/x-cprplayer"      => "pdf",
        ];
        return $allmime[$mime];
    }
}
