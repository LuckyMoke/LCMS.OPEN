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
        $type = in_array($LF['type'], [
            "image", "file", "user",
        ], true) ? $LF['type'] : "";
        if (!$type) {
            if (!LCMS::SUPER()) {
                ajaxout(0, "上传类型不正确");
            }
            $type = is_scalar($LF['type'] ?? "") ? trim(str_replace("\\", "/", $LF['type']), "/ ") : "";
            if (!$type || preg_match('#(^|/)\.{1,2}(/|$)#', $type) || strpos($type, ":") !== false) {
                ajaxout(0, "上传目录不正确");
            }
            $type .= "/";
        }
        if ($_FILES['file']) {
            $res = UPLOAD::file($type, "", "", $LF['force'] > 0 ? true : false, $LF['cid']);
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
        global $_L, $LF;
        $jt    = $_FILES['file'] ?? null;
        $files = (array) ($LF['files'] ?? ($jt ? [$jt] : []));
        $oss   = $_L['plugin']['oss'];
        $list  = [];
        foreach ($files as $url) {
            $source = is_array($url) ? "" : $url;
            if ($source && $oss['type'] != "local" && in_string($source, $oss['domain'])) {
                //已在云存储，无需下载
                $res = [
                    "code" => 1,
                    "src"  => $source,
                ];
            } else {
                $res = UPLOAD::file("image", $url);
            }
            //空值不输出
            $list[] = array_filter([
                "state"  => $res['code'] == 1 ? "SUCCESS" : "FAIL",
                "msg"    => $res['code'] == 1 ? "" : $res['msg'],
                "source" => $source,
                "url"    => $res['src'] ?: "",
            ]);
        }
        if ($jt) {
            unset($list[0]['source']);
            echo json_encode_ex($list[0]);
        } else {
            echo json_encode(["list" => $list]);
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
        if ($_L['plugin']['oss']['type'] == "local") {
            ajaxout(0, "云存储未开启");
        }
        foreach (["file", "type", "oname", "size", "cid"] as $key) {
            if (isset($LF[$key]) && !is_scalar($LF[$key])) {
                ajaxout(0, "参数格式不正确");
            }
        }
        $file = str_replace("\\", "/", (string) $LF['file']);
        $rule = '#^upload/' . intval($_L['ROOTID']) . '/(image|file|user)/(\d{6})/([a-zA-Z0-9_\-]+)\.([a-zA-Z0-9]+)$#';
        if (!preg_match($rule, $file, $match)) {
            ajaxout(0, "文件路径不正确");
        }
        $type  = $match[1];
        $datey = $match[2];
        $name  = $match[3];
        $ext   = strtolower($match[4]);
        if ($LF['type'] && $LF['type'] != $type) {
            ajaxout(0, "上传类型不正确");
        }
        if ($_L['APP']['power']['index']['local'] ?? false) {
            ajaxout(0, "没有上传权限");
        }
        if (!in_array($ext, explode("|", strtolower($_L['config']['admin']['mimelist'])), true)) {
            ajaxout(0, "禁止上传此格式文件");
        }
        $oname = str_replace(["\\", "/"], "", (string) $LF['oname']);
        if (preg_match('/[<>"\']/', $oname)) {
            ajaxout(0, "文件名不能包含 < > \" ' 字符");
        }
        $size = intval($LF['size']);
        $size > 0 || ajaxout(0, "文件大小不正确");
        $this->sql($type, $datey, [
            "cid"   => intval($LF['cid']),
            "oname" => $oname ? mb_substr($oname, 0, 64, "UTF-8") : null,
            "name"  => $name,
            "size"  => $size,
            "src"   => "../" . $file,
        ]);
        ajaxout(1, "上传成功", "", [
            "dir"      => "../upload/" . intval($_L['ROOTID']) . "/{$type}/{$datey}/",
            "filename" => $name,
            "src"      => $_L['plugin']['oss']['domain'] . $file,
            "original" => "../" . $file,
        ]);
    }
    /**
     * @description: 数据库操作
     * @param string $type
     * @param string $datey
     * @param array $data
     * @return {*}
     */
    private function sql($type, $datey, $data = [])
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
            "where" => "id = :id",
            "bind"  => [
                ":id" => intval($_L['ROOTID']),
            ],
            "math" => [
                "storage_used" => "+",
            ],
        ]);
    }
}
