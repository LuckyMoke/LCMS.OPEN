<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-09-08 18:10:13
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
        } elseif ($LC[0]['id']) {
            foreach ($LC as $val) {
                $id = intval($val['id']);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        if ($ids) {
            UPLOAD::del($ids);
        } elseif ($LC['src']) {
            $src = trim($LC['src'], "./ ");
            if (stripos($src, "upload/{$_L['ROOTID']}/") === 0) {
                UPLOAD::del($src);
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
                $token = $Aliyun->policy($LF['dir']);
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
        if (!$data) return;
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
            "math" => [
                "storage_used" => "+",
            ],
        ]);
    }
}
