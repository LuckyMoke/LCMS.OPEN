<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:13
 * @LastEditTime: 2021-12-23 15:00:14
 * @Description: 系统支付操作
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("pays");
class pay extends webbase
{
    protected $paycode;
    public function __construct()
    {
        global $_L, $LF;
        parent::__construct();
        $LF = $_L['form'];
    }
    /**
     * @description: 下单页面
     * @param {*}
     * @return {*}
     */
    public function doindex()
    {
        global $_L, $LF;
        parent::domain($_L['config']['web']['domain_api']);
        PAYS::pay(json_decode(base64_decode($LF['paycode']), true));
    }
    /**
     * @description: 检查订单状态
     * @param {*}
     * @return {*}
     */
    public function docheck()
    {
        global $_L, $LF;
        $order = PAYS::order_info($LF['order_no']);
        if ($order && $order['status'] == 1) {
            ajaxout(1, "支付成功");
        } else {
            ajaxout(0, "待支付");
        }
    }
    /**
     * @description: 支付回调
     * @param {*}
     * @return {*}
     */
    public function donotify()
    {
        global $_L, $LF;
        $result = PAYS::notify($LF['payment']);
        if ($result && $result['order']['order_no']) {
            PAYS::order([
                "order_no" => $result['order']['order_no'],
                "status"   => 1,
                "response" => arr2sql($result['response']),
            ]);
        }
        echo json_encode([
            "code"    => "SUCCESS",
            "message" => "成功",
        ]);
    }
}
