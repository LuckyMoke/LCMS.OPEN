<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-01-04 13:01:43
 * @LastEditTime: 2021-01-04 13:32:20
 * @Description:图标选择器
 * @Copyright 2021 运城市盘石网络科技有限公司
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
    public function doindex()
    {
        global $_L;
        require LCMS::template("own/index");
    }
}
