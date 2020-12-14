<?php
class AutoPay
{
    /**
     * [cfg 必要 支付参数的配置]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function cfg($order)
    {
        global $_L;
        $order['payment']['notify_url'] = "{$_L['url']['site']}core/plugin/payment/alipay/notify.php";
        $order['payment']['return_url'] = $order['other']['return_url'];
        return $order;
    }
    /**
     * [order 必要 下单接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function order($order)
    {
        global $_L;
        $order = self::cfg($order);
        load::plugin("payment/alipay/Config");
        $config = new AliPayConfig($order['payment']);
        /**
         * 计算花呗分期
         */
        if ($order['order']['huabei'] > 0 && $order['order']['huabei'] < 6) {
            $order['order']['huabei'] = 3;
        } elseif ($order['order']['huabei'] > 3 && $order['order']['huabei'] < 12) {
            $order['order']['huabei'] = 6;
        } elseif ($order['order']['huabei'] > 6) {
            $order['order']['huabei'] = 12;
        } else {
            unset($order['order']['huabei']);
        }
        $huabei = $order['order']['huabei'];
        /**
         * 获取用户选择了分几期
         */
        if ($_L['form']['fenqi'] > 0 && $_L['form']['fenqi'] < 6) {
            $order['order']['fenqi'] = 3;
        } elseif ($_L['form']['fenqi'] > 3 && $_L['form']['fenqi'] < 12) {
            $order['order']['fenqi'] = 6;
        } elseif ($_L['form']['fenqi'] > 6) {
            $order['order']['fenqi'] = 12;
        }
        $fenqi = $order['order']['fenqi'];
        switch ($order['order']['paytype']) {
            case 'h5':
            case 'jsapi':
                load::plugin("payment/alipay/type/AliPay.H5");
                $AliPayH5 = new AliPayH5();
                $result   = $AliPayH5->Order($config, $order['order']);
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') !== false) {
                    $newwindows = true;
                }
                require LCMS::template(PATH_CORE_PLUGIN . "payment/alipay/tpl/h5");
                break;
            case 'qr':
                $order['order']['fenqi'] = $huabei;
                load::plugin("payment/alipay/type/AliPay.Qr");
                $AliPayQr = new AliPayQr();
                $result   = $AliPayQr->Order($config, $order['order']);
                if ($result['alipay_trade_precreate_response']['code'] == '10000') {
                    $qrcode = urlencode($result['alipay_trade_precreate_response']['qr_code']);
                    require LCMS::template(PATH_CORE_PLUGIN . "payment/alipay/tpl/qr");
                } else {
                    LCMS::X(403, "创建订单失败：{$result['alipay_trade_precreate_response']['sub_msg']}");
                }
                break;
            case 'app':
                load::plugin("payment/alipay/type/AliPay.App");
                $AliPayApp = new AliPayApp();
                $result    = $AliPayApp->Order($config, $order['order']);
                return $result;
                break;
            case 'pc':
                load::plugin("payment/alipay/type/AliPay.Pc");
                $AliPayPc = new AliPayPc();
                $result   = $AliPayPc->Order($config, $order['order']);
                require LCMS::template(PATH_CORE_PLUGIN . "payment/alipay/tpl/pc");
                break;
        }
    }
    /**
     * [check 必要 查询订单接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function check($order)
    {
        $order = self::cfg($order);
        load::plugin("payment/alipay/Config");
        $config = new AliPayConfig($order['payment']);
        load::plugin("payment/alipay/type/AliPay.Check");
        $AliPayCheck = new AliPayCheck();
        $result      = $AliPayCheck->Check($config, $order['order']);
        if ($result['code'] == "10000" && $result['trade_status'] == "TRADE_SUCCESS") {
            return ["code" => 1, "msg" => "订单已支付", "order_no" => $result['out_trade_no']];
        } else {
            return ["code" => 0, "msg" => "订单未支付：{$result['sub_msg']}{$result['trade_status']}"];
        }
    }
    /**
     * [repay 必要 退款接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function repay($order)
    {
        load::plugin("payment/alipay/Config");
        $config = new AliPayConfig($order['payment']);
        load::plugin("payment/alipay/type/AliPay.Repay");
        $AliPayRepay = new AliPayRepay();
        $result      = $AliPayRepay->Order($config, $order['order']);
        if ($result['alipay_trade_refund_response']['code'] == "10000") {
            return ["code" => 1, "msg" => "退款成功", "order_no" => $result['alipay_trade_refund_response']['out_trade_no']];
        } else {
            return ["code" => 0, "msg" => "退款失败：{$result['alipay_trade_refund_response']['sub_msg']}"];
        }
    }
    /**
     * [notify 必要 支付后回调通知]
     * @return [type] [description]
     */
    public static function notify()
    {
        load::plugin("payment/alipay/type/AliPay.Notify");
        $AliPayNotify = new AliPayNotify();
        return $AliPayNotify->check();
    }
    /**
     * [payto 非必要 转账给个人接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function payto($order)
    {
        load::plugin("payment/alipay/Config");
        $config = new AliPayConfig($order['payment']);
        load::plugin("payment/alipay/type/AliPay.To");
        $AliPayTo = new AliPayTo();
        $result   = $AliPayTo->Order($config, $order['order']);
        if ($result['alipay_trade_refund_response']['code'] == "10000") {
            return ["code" => 1, "msg" => "转账成功", "order_no" => $result['alipay_trade_refund_response']['out_biz_no']];
        } else {
            return ["code" => 0, "msg" => "转账失败：{$result['alipay_trade_refund_response']['sub_msg']}"];
        }
    }
    /**
     * [payto 非必要 转账给个人接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function payto_check($order)
    {
        load::plugin("payment/alipay/Config");
        $config = new AliPayConfig($order['payment']);
        load::plugin("payment/alipay/type/AliPay.To");
        $AliPayTo = new AliPayTo();
        $result   = $AliPayTo->Check($config, $order['order']);
        if ($result['alipay_trade_refund_response']['code'] == "10000") {
            return ["code" => 1, "msg" => "转账成功", "order_no" => $result['alipay_trade_refund_response']['out_biz_no']];
        } else {
            return ["code" => 0, "msg" => "转账失败：{$result['alipay_trade_refund_response']['sub_msg']}"];
        }
    }
}
