<?php
defined('IN_LCMS') or exit('No permission');
class UPLOAD
{
    public static function img($dir, $url = "")
    {
        global $_L;
        if (makedir($dir)) {
            if ($url) {
                $http = http::get($url, true);
                if ($http['code'] == 200 && $http['length'] > 0) {
                    $file    = $http['body'];
                    $formart = str_replace("image/", "", $http['type']);
                    $size    = round($http['length'] / 1024);
                } else {
                    self::out(0, "远程图片下载失败");
                }
            } else {
                $file    = $_FILES['file'];
                $formart = substr($file['name'], strripos($file['name'], ".") + 1);
                $size    = round($file['size'] / 1024);
            }
            if ($size > $_L['config']['admin']['attsize']) {
                $return = self::out(0, "超过上传限制大小");
            } else {
                if (stristr($_L['config']['admin']['mimelist'], $formart) != "") {
                    $filename = date("YmdHis") . microseconds() . "." . $formart;
                    if ($url && file_put_contents($dir . $filename, $file)) {
                        $return = self::out(1, "上传成功", path_relative($dir), $filename);
                    } elseif (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                        $return = self::out(1, "上传成功", path_relative($dir), $filename);
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
    public static function out($code, $msg, $dir = "", $filename = "")
    {
        return array("code" => $code, "msg" => $msg, "dir" => $dir, "filename" => $filename);
    }
}
