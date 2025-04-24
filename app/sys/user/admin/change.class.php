<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-08-17 17:39:57
 * @LastEditTime: 2025-04-14 17:16:28
 * @Description: 修改密码
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::own_class('pub');
class change extends adminbase
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
        switch ($LF['action']) {
            case 'save':
                PUB::userSave(["id", "headimg", "title", "pass"]);
                ajaxout(1, "保存成功", "close");
                break;
        }
    }
}
