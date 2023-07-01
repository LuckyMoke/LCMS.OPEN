<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:13
 * @LastEditTime: 2023-06-26 23:01:45
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
        $LF['payment'] || ajaxout(0, "ERROR");
        $result = PAYS::notify($LF['payment']);
        $order  = $result['order'];
        if ($order['order_no']) {
            PAYS::order([
                "order_no" => $order['order_no'],
                "status"   => 1,
            ]);
            if ($order['callback']) {
                $app   = explode("|", $order['callback']);
                $class = PATH_APP . "open/{$app[0]}/include/class/{$app[1]}.class.php";
                if (is_file($class)) {
                    require_once $class;
                    $fcls = $app[1];
                    if (class_exists($fcls)) {
                        $fcls  = new $fcls();
                        $fname = $app[2];
                        if (method_exists($fcls, $fname)) {
                            $fcls->$fname($order);
                        }
                    }
                }
            }
        }
        echo json_encode([
            "code"    => "SUCCESS",
            "message" => "成功",
        ]);
    }
}
