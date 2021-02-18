<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-02-18 15:00:31
 * @Description:文件上传功能
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
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
    /**
     * @description: 上传图片
     * @param {*}
     * @return {*}
     */
    public function doimg()
    {
        global $_L;
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/image/{$datey}/";
        if ($_FILES['file']) {
            $res = UPLOAD::file($dir);
            if ($res['code'] == "1") {
                $data = [
                    "dir"      => $res['dir'],
                    "filename" => $res['filename'],
                    "src"      => $res['dir'] . $res['filename'],
                ];
                $this->sql("image", $datey, $data);
                ajaxout(1, $res['msg'], "", $data);
            } else {
                ajaxout(0, $res['msg'], "", "");
            }
        }
    }
    /**
     * @description: 删除图片
     * @param {*}
     * @return {*}
     */
    public function dodelimg()
    {
        global $_L;
        $file = $_L['form']['dir'];
        $preg = "./upload/{$_L['ROOTID']}/image";
        if (stripos($file, $preg) !== false) {
            if (delfile($file)) {
                $this->sql("delete", $file);
                ajaxout(1, "删除成功");
            } else {
                ajaxout(0, "删除失败");
            }
        }
        ajaxout(0, "文件不存在");
    }
    /**
     * @description: 上传文件
     * @param {*}
     * @return {*}
     */
    public function dofile()
    {
        global $_L;
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/file/{$datey}/";
        if ($_FILES['file']) {
            $res = UPLOAD::file($dir);
            if ($res['code'] == "1") {
                $data = [
                    "dir"      => $res['dir'],
                    "filename" => $res['filename'],
                    "src"      => $res['dir'] . $res['filename'],
                ];
                $this->sql("file", $datey, $data);
                ajaxout(1, $res['msg'], "", $data);
            } else {
                ajaxout(0, $res['msg'], "", "");
            }
        }
    }
    /**
     * @description: 远程图片多图上传
     * @param {*}
     * @return {*}
     */
    public function doweb()
    {
        global $_L;
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/image/{$datey}/";
        $files = $_L['form']['files'] ?? [];
        foreach ($files as $url) {
            $res = UPLOAD::file($dir, $url);
            if ($res['code'] == 1) {
                $this->sql("image", $datey, [
                    "filename" => $res['filename'],
                    "src"      => $res['dir'] . $res['filename'],
                ]);
                $result[] = [
                    "state"  => "SUCCESS",
                    "source" => $url,
                    "url"    => $res['dir'] . $res['filename'],
                ];
            } else {
                $result[] = [
                    "state"  => "FAIL",
                    "source" => $url,
                ];
            }
        }
        echo json_encode(["list" => $result]);
    }
    /**
     * @description: 数据库操作
     * @param {*} $type
     * @param {*} $datey
     * @param {*} $data
     * @return {*}
     */
    private function sql($type = "image", $datey, $data = "")
    {
        global $_L;
        switch ($type) {
            case 'delete':
                $data = explode("/", $datey);
                sql_delete(["upload", "type = '{$data[3]}' AND datey = '{$data[4]}' AND name = '{$data[5]}' AND lcms = '{$_L['ROOTID']}'"]);
                break;
            default:
                $data && sql_insert(["upload", [
                    "type"  => $type,
                    "datey" => $datey,
                    "name"  => $data['filename'],
                    "src"   => $data['src'],
                    "lcms"  => $_L['ROOTID'],
                ]]);
                break;
        }
    }
}
