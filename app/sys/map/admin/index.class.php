<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-11-19 12:17:09
 * @LastEditTime: 2023-11-19 14:12:53
 * @Description: 地图选择器
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function dotianditu()
    {
        global $_L;
        require LCMS::template("own/tianditu");
    }
}
