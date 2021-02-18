<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-02-18 15:23:47
 * @Description:图库
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
    /**
     * @description: 图片目录列表
     * @param {*}
     * @return {*}
     */
    public function dodirlist()
    {
        global $_L;
        $dir = sql_getall(["upload", "type = 'image' AND lcms = '{$_L['ROOTID']}' GROUP BY datey", "datey DESC"]);
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
        $image = sql_getall(["upload", "type = 'image' AND datey = '{$datey}' AND lcms = '{$_L['ROOTID']}'", "id DESC"]);
        foreach ($image as $val) {
            $size = getimagesize(path_absolute($val['src']));

            $list['file'][] = [
                "name" => $val['name'],
                "src"  => $val['src'],
                "size" => $size[0] . "×" . $size[1],
            ];
        }
        $list['total'] = count($image);
        echo json_encode_ex($list);
    }
}
