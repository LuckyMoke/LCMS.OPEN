<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('upload');
class index extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doimg()
    {
        global $_L;
        $dir = PATH_UPLOAD . $_L['ROOTID'] . "/images/" . date("Ym") . "/";
        if ($_FILES['file']) {
            $upload = UPLOAD::file($dir);
            if ($upload['code'] == "1") {
                ajaxout(1, $upload['msg'], "", [
                    "dir"      => $upload['dir'],
                    "filename" => $upload['filename'],
                    "src"      => $upload['dir'] . $upload['filename'],
                ]);
            } else {
                ajaxout(0, $upload['msg'], "", "");
            }
        }
    }
    public function dodelimg()
    {
        global $_L;
        if ($_L['form']['dir'] && stristr($_L['form']['dir'], "/upload/")) {
            $file = path_absolute($_L['form']['dir']);
            if (delfile($file)) {
                ajaxout(1, "删除成功");
            } else {
                ajaxout(0, "删除失败");
            }
        } else {
            ajaxout(0, "文件不存在");
        }
    }
    public function dofile()
    {
        global $_L;
        $dir = PATH_UPLOAD . $_L['ROOTID'] . "/file/" . date("Ym") . "/";
        if ($_FILES['file']) {
            $upload = UPLOAD::file($dir);
            if ($upload['code'] == "1") {
                ajaxout(1, $upload['msg'], "", [
                    "dir"      => $upload['dir'],
                    "filename" => $upload['filename'],
                    "src"      => $upload['dir'] . $upload['filename'],
                ]);
            } else {
                ajaxout(0, $upload['msg'], "", "");
            }
        }
    }
    public function doweb()
    {
        global $_L;
        $dir = PATH_UPLOAD . $_L['ROOTID'] . "/images/" . date("Ym") . "/";
        if (!empty($_L['form']['files'])) {
            foreach ($_L['form']['files'] as $url) {
                $upload = UPLOAD::file($dir, $url);
                if ($upload['code'] == 1) {
                    $result[] = [
                        "state"  => "SUCCESS",
                        "source" => $url,
                        "url"    => "{$upload['dir']}{$upload['filename']}",
                    ];
                } else {
                    $result[] = [
                        "state"  => "FAIL",
                        "source" => $url,
                    ];
                }
            }
        }
        echo json_encode(["list" => $result]);
    }
}
