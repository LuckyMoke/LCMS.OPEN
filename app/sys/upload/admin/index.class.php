<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-06-27 18:40:39
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
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    /**
     * @description: 上传文件
     * @param {*}
     * @return {*}
     */
    public function dolocal()
    {
        global $_L, $LF, $LC;
        if ($_FILES['file']) {
            $res = UPLOAD::file($LF['type'], "", "", $LF['force'] > 0 ? true : false, $LF['cid']);
        } elseif ($LF['url'] && is_url($LF['url'])) {
            $res = UPLOAD::file("image", $LF['url'], "", $LF['force'] > 0 ? true : false, $LF['cid']);
        }
        if ($res && $res['code'] == 1) {
            unset($res['code'], $res['msg']);
            ajaxout(1, $res['msg'], "", $res);
        } else {
            ajaxout(0, $res['msg'] ?: "上传失败", "", "");
        }
    }
    /**
     * @description: 删除图片
     * @param {*}
     * @return {*}
     */
    public function dodelimg()
    {
        global $_L, $LF, $LC;
        $ids = [];
        if ($LC['id']) {
            $id = intval($LC['id']);
            if ($id > 0) {
                $ids[] = $id;
            }
        } elseif ($LC['src']) {
            $src = trim($LC['src'], "./ ");
            if (stripos($src, "upload/{$_L['ROOTID']}/") === 0) {
                $ids = $src;
            }
        } elseif ($LC[0]['id']) {
            foreach ($LC as $val) {
                $id = intval($val['id']);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        $files = $this->sql("get", $ids);
        $files || ajaxout(0, "无可删除文件");
        $ids   = array_column($files, "id");
        $sizes = array_column($files, "size");
        $files = array_column($files, "src");
        switch ($_L['plugin']['oss']['type']) {
            case 'qiniu':
                load::plugin("Qiniu/QiniuOSS");
                $OSS = new QiniuOSS($_L['plugin']['oss']['qiniu']);
                break;
            case 'tencent':
                load::plugin("Tencent/TencentOSS");
                $OSS = new TencentOSS($_L['plugin']['oss']['tencent']);
                break;
            case 'aliyun':
                load::plugin("Aliyun/AliyunOSS");
                $OSS = new AliyunOSS($_L['plugin']['oss']['aliyun']);
                break;
            case 'baidu':
                load::plugin("Baidu/BaiduOSS");
                $OSS = new BaiduOSS($_L['plugin']['oss']['baidu']);
                break;
        }
        //云存储删除
        if ($OSS) {
            $osfiles = [];
            foreach ($files as $file) {
                $osfiles[] = ltrim($file, "./");
            }
            $OSS->delete($osfiles);
        }
        //本地文件删除
        foreach ($files as $file) {
            delfile($file);
        }
        //数据库删除
        $this->sql("delete", $ids);
        //记录删除日志
        LCMS::log([
            "type" => "system",
            "info" => "删除文件：" . implode(",", $files),
        ]);
        //更新用户存储大小
        if ($_L['ROOTID'] > 0) {
            $sizes = array_sum($sizes);
            $sizes = intval($sizes / 1024);
            $admin = sql_get([
                "table" => "admin",
                "where" => "id = {$_L['ROOTID']}",
            ]);
            if ($admin['storage_used'] >= $sizes) {
                sql_update([
                    "table" => "admin",
                    "data"  => [
                        "storage_used" => $sizes,
                    ],
                    "where" => "id = {$admin['id']}",
                    "math"  => [
                        "storage_used" => "-",
                    ],
                ]);
            } else {
                sql_update([
                    "table" => "admin",
                    "data"  => [
                        "storage_used" => 0,
                    ],
                    "where" => "id = {$admin['id']}",
                ]);
            }
        }
        ajaxout(1, "删除成功", "reload");
    }
    /**
     * @description: 远程图片多图上传
     * @param {*}
     * @return {*}
     */
    public function doeditor()
    {
        global $_L, $LF, $LC;
        $jt    = $_FILES['file'];
        $files = $LF['files'] ?? ($jt ? [$jt] : []);
        foreach ($files as $url) {
            if (!is_array($url) && $_L['plugin']['oss']['type'] != "local" && in_string($url, $_L['plugin']['oss']['domain'])) {
                $result[] = [
                    "state"  => "SUCCESS",
                    "source" => $url,
                    "url"    => $url,
                ];
                continue;
            }
            $res = UPLOAD::file("image", $url);
            if ($res['code'] == 1) {
                $result[] = [
                    "state"  => "SUCCESS",
                    "source" => $url,
                    "url"    => $res['url'] ?: $res['src'],
                ];
            } else {
                $result[] = [
                    "state"  => "FAIL",
                    "msg"    => $res['msg'],
                    "source" => $url,
                ];
            }
        }
        if ($jt) {
            unset($result[0]['source']);
            echo json_encode_ex($result[0]);
        } else {
            echo json_encode(["list" => $result]);
        }
    }
    /**
     * @description: 七牛云上传
     * @param {*}
     * @return {*}
     */
    public function doqiniu()
    {
        global $_L, $LF, $LC;
        load::plugin("Qiniu/QiniuOSS");
        $Qiniu = new QiniuOSS($_L['plugin']['oss']['qiniu']);
        switch ($LF['action']) {
            case 'token':
                ajaxout(1, "success", "", [
                    "token" => $Qiniu->token(),
                ]);
                break;
            case 'success':
                $this->ossSuccess();
                break;
        }
    }
    /**
     * @description: 腾讯云上传
     * @param {*}
     * @return {*}
     */
    public function dotencent()
    {
        global $_L, $LF, $LC;
        load::plugin("Tencent/TencentOSS");
        $Tencent = new TencentOSS($_L['plugin']['oss']['tencent']);
        switch ($LF['action']) {
            case 'token':
                $token           = $Tencent->token();
                $token['Bucket'] = $Tencent->cfg['Bucket'];
                $token['Region'] = $Tencent->cfg['Region'];
                ajaxout(1, "success", "", $token);
                break;
            case 'success':
                $this->ossSuccess();
                break;
        }
    }
    /**
     * @description: 阿里云上传
     * @param {*}
     * @return {*}
     */
    public function doaliyun()
    {
        global $_L, $LF, $LC;
        load::plugin("Aliyun/AliyunOSS");
        $Aliyun = new AliyunOSS($_L['plugin']['oss']['aliyun']);
        switch ($LF['action']) {
            case 'token':
                $token = $Aliyun->token([
                    "method" => "PUT",
                    "path"   => $LF['path'],
                ]);
                ajaxout(1, "success", "", $token);
                break;
            case 'success':
                $this->ossSuccess();
                break;
        }
    }
    /**
     * @description: 百度云上传
     * @param {*}
     * @return {*}
     */
    public function dobaidu()
    {
        global $_L, $LF, $LC;
        load::plugin("Baidu/BaiduOSS");
        $Baidu = new BaiduOSS($_L['plugin']['oss']['baidu']);
        switch ($LF['action']) {
            case 'token':
                $token = $Baidu->token();
                ajaxout(1, "success", "", $token);
                break;
            case 'success':
                $this->ossSuccess();
                break;
        }
    }
    /**
     * @description: 云存储成功后操作
     * @return {*}
     */
    private function ossSuccess()
    {
        global $_L, $LF, $LC;
        $this->sql($LF['type'], $LF['datey'], [
            "cid"   => $LF['cid'],
            "oname" => $LF['oname'] ?: null,
            "name"  => $LF['name'],
            "size"  => $LF['size'],
            "src"   => "../" . $LF['file'],
        ]);
        ajaxout(1, "上传成功", "", [
            "dir"      => "../" . str_replace($LF['name'], "", $LF['file']),
            "filename" => $LF['name'],
            "src"      => $_L['plugin']['oss']['domain'] . $LF['file'],
            "original" => "../" . $LF['file'],
        ]);
    }
    /**
     * @description: 数据库操作
     * @param string $type
     * @param string $datey
     * @param array $data
     * @return {*}
     */
    private function sql($type = "image", $datey, $data = [])
    {
        global $_L, $LF, $LC;
        switch ($type) {
            case 'get':
                if (is_array($datey)) {
                    $ids   = implode(",", $datey);
                    $files = sql_getall([
                        "table"  => "upload",
                        "where"  => "id IN({$ids}) AND lcms = :lcms",
                        "bind"   => [
                            ":lcms" => $_L['ROOTID'],
                        ],
                        "fields" => "id, src, size",
                    ]);
                } else {
                    $date  = explode("/", trim($datey, "./"));
                    $files = sql_get([
                        "table"  => "upload",
                        "where"  => "type = :type AND datey = :datey AND name = :name AND lcms = :lcms",
                        "bind"   => [
                            ":type"  => $date[2],
                            ":datey" => $date[3],
                            ":name"  => $date[4],
                            ":lcms"  => $_L['ROOTID'],
                        ],
                        "fields" => "id, src, size",
                    ]);
                    $files = $files ? [$files] : [];
                }
                return $files ?: [];
                break;
            case 'delete':
                if (is_array($datey)) {
                    $ids = implode(",", $datey);
                    sql_delete([
                        "table" => "upload",
                        "where" => "id IN({$ids}) AND lcms = :lcms",
                        "bind"  => [
                            ":lcms" => $_L['ROOTID'],
                        ],
                    ]);
                } elseif (is_numeric($datey)) {
                    sql_delete([
                        "table" => "upload",
                        "where" => "id = :id AND lcms = :lcms",
                        "bind"  => [
                            ":id"   => $datey,
                            ":lcms" => $_L['ROOTID'],
                        ],
                    ]);
                } else {
                    $date = explode("/", trim($datey, "./"));
                    sql_delete([
                        "table" => "upload",
                        "where" => "type = :type AND datey = :datey AND name = :name AND lcms = :lcms",
                        "bind"  => [
                            ":type"  => $date[2],
                            ":datey" => $date[3],
                            ":name"  => $date[4],
                            ":lcms"  => $_L['ROOTID'],
                        ],
                    ]);
                }
                break;
            default:
                if ($data) {
                    sql_insert(["upload", [
                        "type"    => $type,
                        "cid"     => $data['cid'],
                        "datey"   => $datey,
                        "oname"   => $data['oname'],
                        "name"    => $data['name'],
                        "size"    => $data['size'],
                        "src"     => $data['src'],
                        "addtime" => datenow(),
                        "uid"     => $_L['LCMSADMIN']['id'],
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
                break;
        }
    }
}
