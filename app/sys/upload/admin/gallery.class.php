<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-03-16 12:16:49
 * @Description:图库与编辑器上传组件
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class gallery extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        require LCMS::template("own/gallery");
    }
    public function doupload()
    {
        global $_L;
        require LCMS::template("own/upload");
    }
    public function doivideo()
    {
        global $_L;
        require LCMS::template("own/ivideo");
    }
    public function dovideo()
    {
        global $_L;
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
        global $_L;
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
        global $_L;
        $where = "type = 'image' AND lcms = :lcms";
        if ($_L['ROOTID'] != $_L['LCMSADMIN']['id']) {
            $where .= " AND uid = :uid";
        }
        $dir = sql_getall(["upload",
            "{$where} GROUP BY datey", "datey DESC", [
                ":lcms" => $_L['ROOTID'],
                ":uid"  => $_L['LCMSADMIN']['id'],
            ]]);
        foreach ($dir as $val) {
            $list['dir'][] = $val['datey'];
        }
        $list['total'] = count($list);
        echo json_encode_ex($list);
    }
    /**
     * @description: 图片列表
     * @param {*}
     * @return {*}
     */
    public function dofilelist()
    {
        global $_L;
        $datey = $_L['form']['dir'];
        $where = "type = 'image' AND lcms = :lcms";
        if ($_L['ROOTID'] != $_L['LCMSADMIN']['id']) {
            $where .= " AND uid = :uid";
        }
        $image = sql_getall(["upload",
            "{$where} AND datey = :datey", "id DESC", [
                ":lcms"  => $_L['ROOTID'],
                ":uid"   => $_L['LCMSADMIN']['id'],
                ":datey" => $datey,
            ]]);
        foreach ($image as $val) {
            $size = getimagesize(path_absolute($val['src']));
            switch ($_L['plugin']['oss']['type']) {
                case 'qiniu':
                case 'tencent':
                case 'aliyun':
                    $list['file'][] = [
                        "name"    => $val['name'],
                        "src"     => $_L['plugin']['oss']['domain'] . str_replace("../", "", $val['src']),
                        "datasrc" => $val['src'],
                        "size"    => '',
                        "stylle"  => 'opacity:0',
                    ];
                    break;
                default:
                    $list['file'][] = [
                        "name"    => $val['name'],
                        "src"     => $val['src'],
                        "datasrc" => $val['src'],
                        "size"    => $size[0] . "×" . $size[1],
                    ];
                    break;
            }

        }
        $list['total'] = count($image);
        echo json_encode_ex($list);
    }
}
