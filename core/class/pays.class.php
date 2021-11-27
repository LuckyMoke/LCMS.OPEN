<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-11-26 18:49:58
 * @Description:下单支付操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PAYS
{
    /**
     * [order_info 查询订单信息]
     * @param  [type] $order_no [description]
     * @return [type]           [description]
     */
    public static function order_info($order_no)
    {
        $order = sql_get([
            "order",
            "order_no = :order_no",
            "", [
                ":order_no" => $order_no,
            ],
        ]);
        return $order;
    }
    /**
     * [payment_info 查询支付信息]
     * @param  [type] $payid [description]
     * @return [type]        [description]
     */
    public static function payment_info($payid, $type = false)
    {
        global $_L;
        $payment = LCMS::form([
            "table" => "payment",
            "do"    => "get",
            "id"    => $payid,
        ]);
        if ($type) {
            return $payment;
        } else {
            load::plugin("payment/{$payment['payment']}/AutoPay");
            $payment = AutoPay::init([
                "payment" => $payment[$payment['payment']],
            ]);
            return $payment['payment'];
        }
    }
    /**
     * [payment_list 获取支付方式列表]
     * @param  string $payment [description]
     * @return [type]          [description]
     */
    public static function payment_list($payment = "")
    {
        global $_L;
        if ($payment) {
            $list = sql_getall([
                "payment",
                "payment LIKE :payment AND lcms = '{$_L['ROOTID']}'",
                "id DESC",
                [
                    ":payment" => "{$payment}%",
                ],
            ]);
        } else {
            $list = sql_getall([
                "payment",
                "lcms = '{$_L['ROOTID']}'",
                "id DESC",
            ]);
        }
        foreach ($list as $key => $val) {
            $result[] = [
                "title" => $val['title'],
                "value" => $val['id'],
            ];
        }
        return $result;
    }
    /**
     * [payment_config 获取支付方式配置文件]
     * @param  string $payment [description]
     * @return [type]          [description]
     */
    public static function payment_config($payment = "")
    {
        global $_L;
        $dir = PATH_CORE_PLUGIN . "payment/";
        if ($payment) {
            # code...
        } else {
            $payment = traversal_one($dir);
            if (is_array($payment['dir'])) {
                foreach ($payment['dir'] as $key => $val) {
                    if (is_file($dir . $val . "/config.json")) {
                        $config[$val] = file_get_contents($dir . $val . "/config.json");
                    }
                }
            }
        }
        return $config;
    }
    /**
     * [order 下单、更新订单操作]
     * @param  array  $order [description]
     * @return [type]        [description]
     */
    public static function order($order = [])
    {
        if ($order['order_no']) {
            if ($order['status'] == "1") {
                $order['paytime'] = datenow();
            } elseif ($order['status'] == "2") {
                $order['repaytime'] = datenow();
            }
            sql_update(["order",
                $order, "order_no = :order_no", [
                    ":order_no" => $order['order_no'],
                ],
            ]);
        } elseif (!$order['payment'] && $order['payid']) {
            $payment           = self::payment_info($order['payid'], true);
            $order['order_no'] = randstr(4) . date("YmdHis") . microseconds() . randstr(2, "num");
            $order['payment']  = $payment['payment'];
            $order['addtime']  = datenow();
            sql_insert(["order", $order]);
        } else {
            return false;
        }
        return self::order_info($order['order_no']);
    }
    /**
     * @description: 获取支付页面链接
     * @param array $para
     * @return string
     */
    public static function get($para = array())
    {
        global $_L;
        $order = $para['order_no'] ? self::order_info($para['order_no']) : self::order([
            "body"    => $para['body'],
            "pay"     => $para['pay'],
            "paytype" => $para['paytype'],
            "payid"   => $para['payid'],
        ]);
        if ($order) {
            $paycode = urlencode(base64_encode(json_encode_ex([
                "order_no"     => $order['order_no'],
                "order_no_own" => $para['order_no_own'],
                "payid"        => $order['payid'],
                "parameter"    => $para['parameter'],
                "goback"       => $para['goback'],
            ])));
            $url = "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=system&c=pay&paycode={$paycode}";
            return $url;
        }
    }
    /**
     * [pay 发起支付操作]
     * @param  [type] $paycode [description]
     * @param  string $openid  [可选传入OPENID值，小程序使用]
     * @return [type]          [description]
     */
    public static function pay($paycode, $openid = "")
    {
        $payment = self::payment_info($paycode['payid'], true);
        $payment ? "" : LCMS::X(404, "未找到支付参数");
        $order = $paycode['order_no'] ? self::order([
            "order_no" => $paycode['order_no'],
            "payment"  => $payment['payment'],
        ]) : LCMS::X(404, "未找到订单信息");
        if ($order['status'] == "0") {
            $order['openid'] = $openid ? $openid : false;
            load::plugin("payment/{$order['payment']}/AutoPay");
            $order  = $paycode['parameter'] ? array_merge($order, $paycode['parameter']) : $order;
            $result = AutoPay::order([
                "payment" => $payment[$order['payment']],
                "order"   => $order,
                "other"   => [
                    "order_no_own" => $paycode['order_no_own'],
                    "return_url"   => $paycode['goback'],
                ],
            ]);
            return $result;
        } else {
            goheader($paycode['goback']);
        }
    }
    /**
     * [check 订单支付状态检测]
     * @param  [type] $order_no [description]
     * @return [type]           [description]
     */
    public static function check($order_no)
    {
        global $_L;
        $order   = self::order_info($order_no);
        $payment = self::payment_info($order['payid'], true);
        if ($order && $payment && $order['status'] == "1") {
            return ["code" => 1, "msg" => "订单已支付"];
        } elseif ($order && $payment) {
            load::plugin("payment/{$order['payment']}/AutoPay");
            $result = AutoPay::check([
                "payment" => $payment[$order['payment']],
                "order"   => $order,
            ]);
            if ($result['code'] == "1" && $result['order_no']) {
                self::order([
                    "order_no" => $result['order_no'],
                    "paytime"  => datenow(),
                    "status"   => "1",
                ]);
            }
            return $result;
        } else {
            return ["code" => 0, "msg" => "未找到订单信息"];
        }
    }
    /**
     * [repay 退款操作]
     * @param  [type] $order_no [description]
     * @return [type]           [description]
     */
    public static function repay($order_no)
    {
        global $_L;
        $order   = self::order_info($order_no);
        $payment = self::payment_info($order['payid'], true);
        if ($order && $payment && $order['status'] == "1") {
            load::plugin("payment/{$order['payment']}/AutoPay");
            $result = AutoPay::repay([
                "payment" => $payment[$order['payment']],
                "order"   => $order,
            ]);
            if ($result['code'] == "1" && $result['order_no']) {
                self::order([
                    "order_no"  => $result['order_no'],
                    "repaytime" => datenow(),
                    "status"    => "2",
                ]);
            }
            return $result;
        } else {
            return ["code" => 0, "msg" => "退款失败：未支付或已退款"];
        }
    }
    /**
     * [notify 支付结果验证回调]
     * @param  [type] $payment [description]
     * @return [type]          [description]
     */
    public static function notify($payment)
    {
        load::plugin("payment/{$payment}/AutoPay");
        return AutoPay::notify();
    }
}
