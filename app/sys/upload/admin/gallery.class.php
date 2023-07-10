<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-07-04 12:41:25
 * @Description:图库与编辑器上传组件
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class gallery extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/gallery");
    }
    public function doupload()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/upload");
    }
    public function doivideo()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/ivideo");
    }
    public function dovideo()
    {
        global $_L, $LF, $LC;
        $form = [
            ["layui" => "file", "title" => "视频链接",
                "name"   => "src",
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
                "value"  => "800",
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
    public function doattachment()
    {
        global $_L, $LF, $LC;
        $form = [
            ["layui" => "file", "title" => "上传附件",
                "name"   => "file",
                "verify" => "required"],
            ["layui" => "btn", "title" => "插入附件"],
        ];
        require LCMS::template("own/attachment");
    }
    /**
     * @description: 图片目录列表
     * @param {*}
     * @return {*}
     */
    public function dodirlist()
    {
        global $_L, $LF, $LC;
        $where = "type = 'image' AND lcms = {$_L['ROOTID']}";
        $where .= $_L['ROOTID'] != $_L['LCMSADMIN']['id'] ? " AND uid = {$_L['LCMSADMIN']['id']}" : "";
        $where .= " GROUP BY datey";
        if ($LF['page'] <= 1) {
            $total = sql_query("SELECT COUNT(*) FROM (SELECT COUNT(*) FROM {$_L['table']['upload']} WHERE {$where}) AS total")['COUNT(*)'];
            $total <= 0 && ajaxout(0, "error");
        }
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
        $where .= " AND datey = :datey";
        $bind = [
            ":datey" => $LF['dir'],
        ];
        if ($LF['page'] <= 1) {
            $total = sql_counter([
                "table" => "upload",
                "where" => $where,
                "bind"  => $bind,
            ]);
            $total <= 0 && ajaxout(0, "error");
        }
        $image = sql_getall([
            "table" => "upload",
            "where" => $where,
            "order" => "id DESC",
            "bind"  => $bind,
            "limit" => [($LF['page'] - 1) * $LF['limit'], $LF['limit']],
        ]);
        foreach ($image as $val) {
            $size = getimagesize(path_absolute($val['src']));
            switch ($_L['plugin']['oss']['type']) {
                case '':
                case 'local':
                    $list[] = [
                        "name"    => $val['name'],
                        "src"     => $val['src'],
                        "datasrc" => $val['src'],
                        "size"    => $size[0] . "×" . $size[1],
                    ];
                    break;
                default:
                    $list[] = [
                        "name"    => $val['name'],
                        "src"     => oss($val['src']),
                        "datasrc" => $val['src'],
                        "size"    => '',
                        "stylle"  => 'opacity:0',
                    ];
                    break;
            }

        }
        ajaxout(1, "success", "", [
            "list"  => $list ?: [],
            "total" => $total ? intval($total) : 0,
        ]);
    }
}
