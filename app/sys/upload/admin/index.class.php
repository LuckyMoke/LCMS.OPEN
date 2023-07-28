<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-07-28 10:32:02
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
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/{$LF['type']}/{$datey}/";
        if ($_FILES['file']) {
            $res = UPLOAD::file($dir, "", "", $LF['force'] > 0 ? 1 : 0);
            if ($res['code'] == 1) {
                unset($res['code'], $res['msg']);
                $this->sql($LF['type'], $datey, $res);
                ajaxout(1, $res['msg'], "", $res);
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
        if ($LC && $LC['src']) {
            $files = [$LC['src']];
        } elseif ($LC && $LC[0] && $LC[0]['src']) {
            $ids   = implode(",", array_column($LC, "id"));
            $files = array_column($LC, "src");
        } else {
            $files = [$LF['dir']];
        }
        foreach ($files as $index => $file) {
            if (in_string($file, "upload/{$_L['ROOTID']}/")) {
                $files[$index] = str_replace(["../", "./"], "", $file);
            } else {
                unset($files[$index]);
            }
        }
        $files = array_values($files);
        if ($files) {
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
            }
        }
        if ($OSS) {
            $OSS->delete($files);
        } else {
            foreach ($files as $file) {
                delfile("../{$file}");
            }
        }
        if ($ids) {
            $this->sql("deletebyid", $ids);
        } else {
            $this->sql("delete", $files[0]);
        }
        sql_error() && ajaxout(0, "删除失败");
        LCMS::log([
            "type" => "system",
            "info" => "删除文件-" . implode(",", $files),
        ]);
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
        $datey = date("Ym");
        $dir   = PATH_UPLOAD . "{$_L['ROOTID']}/image/{$datey}/";
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
            $res = UPLOAD::file($dir, $url);
            if ($res['code'] == 1) {
                $result[] = [
                    "state"  => "SUCCESS",
                    "source" => $url,
                    "url"    => $res['url'] ?: $res['src'],
                ];
                $this->sql("image", $datey, $res);
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
                $token = $Aliyun->token();
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
            "filename" => $LF['name'],
            "size"     => $LF['size'],
            "src"      => "../" . $LF['file'],
        ]);
        ajaxout(1, "上传成功", "", [
            "dir"      => "../" . str_replace($LF['name'], "", $LF['file']),
            "filename" => $LF['name'],
            "src"      => $_L['plugin']['oss']['domain'] . $LF['file'],
            "datasrc"  => "../" . $LF['file'],
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
            case 'delete':
                $data = explode("/", $datey);
                sql_delete([
                    "upload",
                    "type = :type AND datey = :datey AND name = :name AND lcms = :lcms",
                    [
                        ":type"  => $data[2],
                        ":datey" => $data[3],
                        ":name"  => $data[4],
                        ":lcms"  => $_L['ROOTID'],
                    ],
                ]);
                break;
            case 'deletebyid':
                $datey && sql_delete([
                    "upload",
                    "id IN ({$datey}) AND lcms = :lcms",
                    [
                        ":lcms" => $_L['ROOTID'],
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
