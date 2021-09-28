<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-09-28 15:20:17
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
     * @description: 上传图片
     * @param {*}
     * @return {*}
     */
    public function dolocal()
    {
        global $_L, $LF;
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/{$LF['type']}/{$datey}/";
        if ($_FILES['file']) {
            $res = UPLOAD::file($dir);
            if ($res['code'] == "1") {
                $data = [
                    "dir"      => $res['dir'],
                    "filename" => $res['filename'],
                    "size"     => $res['size'],
                    "src"      => $res['dir'] . $res['filename'],
                ];
                $this->sql($LF['type'], $datey, $data);
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
        global $_L, $LF, $LC;
        $file = $LC['src'] ?: $LF['dir'];
        $file = str_replace(["../", "./"], "", $file);
        $preg = "upload/{$_L['ROOTID']}/";
        if (stripos($file, $preg) !== false) {
            switch ($_L['plugin']['oss']['type']) {
                case 'qiniu':
                    load::plugin("Qiniu/QiniuOSS");
                    $Qiniu = new QiniuOSS($_L['plugin']['oss']['qiniu']);
                    $Qiniu->delete($file);
                    $this->sql("delete", "../{$file}");
                    ajaxout(1, "删除成功", "reload");
                    break;
                case 'tencent':
                    load::plugin("Tencent/TencentOSS");
                    $Tencent = new TencentOSS($_L['plugin']['oss']['tencent']);
                    $Tencent->delete($file);
                    $this->sql("delete", "../{$file}");
                    ajaxout(1, "删除成功", "reload");
                    break;
                case 'aliyun':
                    load::plugin("Aliyun/AliyunOSS");
                    $Aliyun = new AliyunOSS($_L['plugin']['oss']['aliyun']);
                    $Aliyun->delete($file);
                    $this->sql("delete", "../{$file}");
                    ajaxout(1, "删除成功", "reload");
                    break;
                default:
                    $this->sql("delete", "../{$file}");
                    if (!sql_error()) {
                        delfile("../{$file}");
                        ajaxout(1, "删除成功", "reload");
                    } else {
                        ajaxout(0, "删除失败");
                    }
                    break;
            }
        }
        ajaxout(0, "文件不存在");
    }
    /**
     * @description: 远程图片多图上传
     * @param {*}
     * @return {*}
     */
    public function doeditor()
    {
        global $_L;
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/image/{$datey}/";
        $files = $_L['form']['files'] ?? [];
        foreach ($files as $url) {
            if ($_L['plugin']['oss']['type'] != "local" && stripos($url, $_L['plugin']['oss']['domain']) !== false) {
                $result[] = [
                    "state"  => "SUCCESS",
                    "source" => $url,
                    "url"    => $url,
                ];
                continue;
            }
            $res = UPLOAD::file($dir, $url);
            if ($res['code'] == 1) {
                $path = $res['dir'] . $res['filename'];
                switch ($_L['plugin']['oss']['type']) {
                    case 'qiniu':
                        load::plugin("Qiniu/QiniuOSS");
                        $Qiniu = new QiniuOSS($_L['plugin']['oss']['qiniu']);
                        $rst   = $Qiniu->upload($path);
                        if ($rst['code'] == "1") {
                            $result[] = [
                                "state"  => "SUCCESS",
                                "source" => $url,
                                "url"    => $_L['plugin']['oss']['domain'] . str_replace("../", "", $path),
                            ];
                            delfile($path);
                        }
                        break;
                    case 'tencent':
                        load::plugin("Tencent/TencentOSS");
                        $Tencent = new TencentOSS($_L['plugin']['oss']['tencent']);
                        $rst     = $Tencent->upload($path);
                        if ($rst['code'] == "1") {
                            $result[] = [
                                "state"  => "SUCCESS",
                                "source" => $url,
                                "url"    => $_L['plugin']['oss']['domain'] . str_replace("../", "", $path),
                            ];
                            delfile($path);
                        }
                        break;
                    case 'aliyun':
                        load::plugin("Aliyun/AliyunOSS");
                        $Aliyun = new AliyunOSS($_L['plugin']['oss']['aliyun']);
                        $rst    = $Aliyun->upload($path);
                        if ($rst['code'] == "1") {
                            $result[] = [
                                "state"  => "SUCCESS",
                                "source" => $url,
                                "url"    => $_L['plugin']['oss']['domain'] . str_replace("../", "", $path),
                            ];
                            delfile($path);
                        }
                        break;
                    default:
                        $result[] = [
                            "state"  => "SUCCESS",
                            "source" => $url,
                            "url"    => $path,
                        ];
                        break;
                }
                $this->sql("image", $datey, [
                    "filename" => $res['filename'],
                    "size"     => $res['size'],
                    "src"      => $path,
                ]);
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
     * @description: 七牛云上传
     * @param {*}
     * @return {*}
     */
    public function doqiniu()
    {
        global $_L, $LF;
        load::plugin("Qiniu/QiniuOSS");
        $Qiniu = new QiniuOSS($_L['plugin']['oss']['qiniu']);
        switch ($LF['action']) {
            case 'token':
                ajaxout(1, "success", "", [
                    "token" => $Qiniu->token(),
                ]);
                break;
            case 'success':
                $this->sql($LF['type'], $LF['datey'], [
                    "filename" => $LF['name'],
                    "size"     => $LF['size'],
                    "src"      => "../" . $LF['file'],
                ]);
                ajaxout(1, "上传成功", "", [
                    "dir"      => "../" . $LF['dir'],
                    "filename" => $LF['name'],
                    "src"      => $_L['plugin']['oss']['domain'] . $LF['file'],
                    "datasrc"  => "../" . $LF['file'],
                ]);
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
        global $_L, $LF;
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
                $this->sql($LF['type'], $LF['datey'], [
                    "filename" => $LF['name'],
                    "size"     => $LF['size'],
                    "src"      => "../" . $LF['file'],
                ]);
                ajaxout(1, "上传成功", "", [
                    "dir"      => "../" . $LF['dir'],
                    "filename" => $LF['name'],
                    "src"      => $_L['plugin']['oss']['domain'] . $LF['file'],
                    "datasrc"  => "../" . $LF['file'],
                ]);
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
        global $_L, $LF;
        load::plugin("Aliyun/AliyunOSS");
        $Aliyun = new AliyunOSS($_L['plugin']['oss']['aliyun']);
        switch ($LF['action']) {
            case 'token':
                $token = $Aliyun->token();
                ajaxout(1, "success", "", $token);
                break;
            case 'success':
                $this->sql($LF['type'], $LF['datey'], [
                    "filename" => $LF['name'],
                    "size"     => $LF['size'],
                    "src"      => "../" . $LF['file'],
                ]);
                ajaxout(1, "上传成功", "", [
                    "dir"      => "../" . $LF['dir'],
                    "filename" => $LF['name'],
                    "src"      => $_L['plugin']['oss']['domain'] . $LF['file'],
                    "datasrc"  => "../" . $LF['file'],
                ]);
                break;
        }
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
        global $_L;
        switch ($type) {
            case 'delete':
                $data = explode("/", $datey);
                sql_delete([
                    "upload",
                    "type = :type AND datey = :datey AND name = :name AND lcms = '{$_L['ROOTID']}'",
                    [
                        ":type"  => $data[3],
                        ":datey" => $data[4],
                        ":name"  => $data[5],
                    ],
                ]);
                break;
            default:
                $data && sql_insert(["upload", [
                    "type"    => $type,
                    "datey"   => $datey,
                    "name"    => $data['filename'],
                    "size"    => $data['size'],
                    "src"     => $data['src'],
                    "addtime" => datenow(),
                    "uid"     => $_L['LCMSADMIN']['id'],
                    "lcms"    => $_L['ROOTID'],
                ]]);
                break;
        }
    }
}
