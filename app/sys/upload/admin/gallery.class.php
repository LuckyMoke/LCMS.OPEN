<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-09-24 18:25:56
 * @Description:图库与编辑器上传组件
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
LOAD::sys_class('adminbase');
LOAD::sys_class("table");
class gallery extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    /**
     * @description: 图库列表
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/gallery");
    }
    /**
     * @description: 上传图片
     * @return {*}
     */
    public function doupload()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/upload");
    }
    /**
     * @description: 图片裁剪
     * @return {*}
     */
    public function docrop()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/crop");
    }
    /**
     * @description: 第三方视频
     * @return {*}
     */
    public function doivideo()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/ivideo");
    }
    /**
     * @description: 上传视频
     * @return {*}
     */
    public function dovideo()
    {
        global $_L, $LF, $LC;
        $form = [
            ["layui" => "file", "title" => "视频链接",
                "name"   => "src",
                "mime"   => "video",
                "verify" => "required"],
            ["layui" => "upload", "title" => "视频封面",
                "name"   => "poster"],
            ["layui" => "radio", "title" => "自动播放",
                "name"   => "autoplay",
                "radio"  => [
                    ["title" => "否", "value" => 0],
                    ["title" => "是", "value" => 1],
                ]],
            ["layui" => "radio", "title" => "循环播放",
                "name"   => "loop",
                "radio"  => [
                    ["title" => "否", "value" => 0],
                    ["title" => "是", "value" => 1],
                ]],
            ["layui" => "input", "title" => "视频宽",
                "name"   => "width",
                "value"  => "100%",
                "tips"   => "数字或100%",
                "verify" => "required"],
            ["layui" => "input", "title" => "视频高",
                "name"   => "height",
                "value"  => "auto",
                "tips"   => "数字或auto"],
            ["layui" => "btn", "title" => "插入视频"],
        ];
        require LCMS::template("own/video");
    }
    /**
     * @description: 上传附件
     * @return {*}
     */
    public function doattachment()
    {
        global $_L, $LF, $LC;
        $form = [
            ["layui" => "input", "title" => "附件名称",
                "name"   => "title",
                "verify" => "required"],
            ["layui" => "file", "title" => "上传附件",
                "name"   => "file",
                "verify" => "required"],
            ["layui" => "btn", "title" => "插入附件"],
        ];
        require LCMS::template("own/attachment");
    }
    /**
     * @description: 附件列表选择
     * @return {*}
     */
    public function doattachmentlist()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'list':
                $doamin = $_L['plugin']['oss']['type'] != "local" ? $_L['plugin']['oss']['domain'] : $_L['url']['site'];
                $where  = $LC['name'] ? " AND name LIKE :name" : "";
                $data   = TABLE::set("upload", "lcms = :lcms AND type = 'file' AND local = 0 AND uid = :uid{$where}", "id DESC", [
                    ":lcms" => $_L['ROOTID'],
                    ":uid"  => $_L['LCMSADMIN']['id'],
                    ":name" => "%{$LC['name']}%",
                ]);
                foreach ($data as $index => $val) {
                    $src          = str_replace("../", "", $val['src']);
                    $data[$index] = array_merge($val, [
                        "size"  => getunit($val['size']),
                        "oname" => $val['oname'] ?: '<span style="color:#cccccc">无</span>',
                        "href"  => [
                            "type"   => "link",
                            "title"  => "查看",
                            "url"    => ($val['local'] == 1 ? $_L['url']['site'] : $doamin) . $src,
                            "target" => "_blank",
                        ],
                    ]);
                }
                TABLE::out($data);
                break;
            default:
                $table = [
                    "url"    => "attachmentlist&action=list",
                    "cols"   => [
                        ["title" => "存储文件名", "field" => "name",
                            "width"  => 180],
                        ["title"   => "原始文件名", "field" => "oname",
                            "minWidth" => 200],
                        ["title" => "文件大小", "field" => "size",
                            "width"  => 100,
                            "align"  => "center"],
                        ["title" => "上传时间", "field" => "addtime",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title" => "查看", "field" => "href",
                            "align"  => "center",
                            "width"  => 70],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 70,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title" => "选择", "event" => "choice"],
                            ]],
                    ],
                    "search" => [
                        ["title" => "存储文件名", "name" => "name"],
                    ],
                ];
                require LCMS::template("own/attachmentlist");
                break;
        }
    }
    /**
     * @description: 图片目录列表
     * @param {*}
     * @return {*}
     */
    public function dodirlist()
    {
        global $_L, $LF, $LC;
        $where = "type = 'image' AND cid = 0 AND lcms = {$_L['ROOTID']}";
        $where .= $_L['ROOTID'] != $_L['LCMSADMIN']['id'] ? " AND uid = {$_L['LCMSADMIN']['id']}" : "";
        if ($LF['page'] <= 1) {
            $total = sql_get([
                "table"  => "upload",
                "where"  => $where,
                "fields" => "COUNT(DISTINCT datey) AS total",
            ]);
            $total = $total['total'] ?: 0;
        }
        $where .= " GROUP BY datey";
        $list = sql_getall([
            "table"  => "upload",
            "where"  => $where,
            "order"  => "datey DESC",
            "fields" => "datey",
            "limit"  => [($LF['page'] - 1) * $LF['limit'], $LF['limit']],
        ]);
        ajaxout(1, "success", "", [
            "list"  => $list ? array_column($list, "datey") : [],
            "total" => $total ? intval($total) : 0,
        ]);
    }
    /**
     * @description: 图片列表
     * @param {*}
     * @return {*}
     */
    public function dofilelist()
    {
        global $_L, $LF, $LC;
        $where = "type = 'image' AND lcms = {$_L['ROOTID']}";
        $where .= $_L['ROOTID'] != $_L['LCMSADMIN']['id'] ? " AND uid = {$_L['LCMSADMIN']['id']}" : "";
        if (in_string($LF['dir'], "custom")) {
            $cid = str_replace("custom_", "", $LF['dir']);
            $cid = intval($cid);
            $where .= " AND cid = :cid";
            $bind = [
                ":cid" => $cid,
            ];
        } else {
            $where .= " AND cid = 0 AND datey = :datey";
            $bind = [
                ":datey" => $LF['dir'],
            ];
        }
        if ($LF['page'] <= 1) {
            $total = sql_counter([
                "table" => "upload",
                "where" => $where,
                "bind"  => $bind,
            ]);
        }
        $image = sql_getall([
            "table" => "upload",
            "where" => $where,
            "order" => "id DESC",
            "bind"  => $bind,
            "limit" => [($LF['page'] - 1) * $LF['limit'], $LF['limit']],
        ]);
        foreach ($image as $val) {
            switch ($_L['plugin']['oss']['type']) {
                case '':
                case 'local':
                    $src = $val['src'];
                    break;
                default:
                    $src = $val['local'] == 1 ? $val['src'] : oss($val['src']);
                    break;
            }
            $list[] = [
                "oname"    => $val['oname'] ?: "无",
                "name"     => $val['name'],
                "src"      => $src,
                "original" => $val['src'],
                "size"     => $val['size'] > 0 ? getunit($val['size']) : "未知",
            ];
        }
        ajaxout(1, "success", "", [
            "list"  => $list ?: [],
            "total" => $total ? intval($total) : 0,
        ]);
    }
    /**
     * @description: 图片图库列表
     * @param {*}
     * @return {*}
     */
    public function doclasslist()
    {
        global $_L, $LF, $LC;
        $list = sql_getall([
            "table" => "upload_class",
            "where" => "lcms = :lcms",
            "order" => "id ASC",
            "bind"  => [
                ":lcms" => $_L['ROOTID'],
            ],
        ]);
        foreach ($list as $index => $val) {
            $list[$index] = [
                "id"    => $val['id'],
                "title" => $val['title'],
                "dir"   => "custom_{$val['id']}",
                "path"  => "",
            ];
        }
        ajaxout([
            "code" => 1,
            "msg"  => "success",
            "data" => $list,
        ]);
    }
    /**
     * @description: 删除图库
     * @param {*}
     * @return {*}
     */
    public function doclassdel()
    {
        global $_L, $LF, $LC;
        sql_update([
            "table" => "upload",
            "where" => "cid = :cid",
            "data"  => [
                "cid" => 0,
            ],
            "bind"  => [
                ":cid" => $LF['id'],
            ],
        ]);
        sql_delete([
            "table" => "upload_class",
            "where" => "id = :id",
            "bind"  => [
                ":id" => $LF['id'],
            ],
        ]);
        ajaxout(1, "图库已删除，所有图片已放入默认图库中");
    }
    /**
     * @description: 新增、编辑图库
     * @param {*}
     * @return {*}
     */
    public function doclassedit()
    {
        global $_L, $LF, $LC;
        if ($LF['id']) {
            sql_update([
                "table" => "upload_class",
                "data"  => [
                    "title" => $LF['title'],
                    "lcms"  => $_L['ROOTID'],
                ],
                "where" => "id = :id",
                "bind"  => [
                    ":id" => $LF['id'],
                ],
            ]);
            sql_error() && ajaxout(0, "图库修改失败");
            $msg = "图库修改成功";
        } else {
            $LF['id'] = sql_insert([
                "table" => "upload_class",
                "data"  => [
                    "title" => $LF['title'],
                    "lcms"  => $_L['ROOTID'],
                ],
            ]);
            sql_error() && ajaxout(0, "图库添加失败");
            $msg = "图库添加成功";
        }
        ajaxout([
            "code" => 1,
            "msg"  => $msg,
            "data" => [
                "id"    => $LF['id'],
                "title" => $LF['title'],
                "dir"   => "custom_{$LF['id']}",
                "path"  => "",
            ],
        ]);
    }
}
