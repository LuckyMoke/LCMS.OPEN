<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-25 12:28:04
 * @LastEditTime: 2023-06-25 12:30:33
 * @Description: PUB公共类
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PUB
{
    /**
     * @description: id转token
     * @param string|int $id
     * @return string
     */
    public static function id2token($id)
    {
        global $_L;
        return ssl_encode($id, $_L['LCMSADMIN']['salt']);
    }
    /**
     * @description: token转id
     * @param string $token
     * @return string|int
     */
    public static function token2id($token = "")
    {
        global $_L;
        $id = $token ? ssl_decode($token, $_L['LCMSADMIN']['salt']) : "";
        $id = intval($id);
        return $id > 0 ? $id : "";
    }
}
