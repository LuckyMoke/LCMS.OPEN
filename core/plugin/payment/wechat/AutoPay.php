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
        $order['payment']['notify_url']     = "{$_L['url']['site']}core/plugin/payment/wechat/notify.php";
        $order['payment']['apiclient_cert'] = PATH_CORE_PLUGIN . "payment/wechat/cert/" . md5($order['payment']['mch_id']) . "_cert.pem";
        $order['payment']['apiclient_key']  = PATH_CORE_PLUGIN . "payment/wechat/cert/" . md5($order['payment']['mch_id']) . "_key.pem";
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
        load::plugin("payment/wechat/Config");
        $config = new WxPayConfig($order['payment']);
        switch ($order['order']['paytype']) {
            case 'jsapi':
                load::plugin("payment/wechat/type/WxPay.Jsapi");
                $WxPayJsapi = new WxPayJsapi();
                $result     = $WxPayJsapi->Order($config, $order['order']);
                require LCMS::template(PATH_CORE_PLUGIN . "payment/wechat/tpl/jsapi");
                break;
            case 'h5':
                load::plugin("payment/wechat/type/WxPay.H5");
                $WxPayH5 = new WxPayH5();
                $result  = $WxPayH5->Order($config, $order['order']);
                if ($result['mweb_url']) {
                    goheader($result['mweb_url']);
                } else {
                    LCMS::X($result['return_code'], $result['return_msg']);
                }
                break;
            case 'mini':
                load::plugin("payment/wechat/type/WxPay.Mini");
                $WxPayMini = new WxPayMini();
                $result    = $WxPayMini->Order($config, $order['order']);
                return $result;
                break;
            case 'pc':
                load::plugin("payment/wechat/type/WxPay.Pc");
                $WxPayPc = new WxPayPc();
                $result  = $WxPayPc->Order($config, $order['order']);
                if ($result['code_url']) {
                    $qrcode = urlencode($result['code_url']);
                    require LCMS::template(PATH_CORE_PLUGIN . "payment/wechat/tpl/pc");
                } else {
                    LCMS::X($result['return_code'], $result['return_msg']);
                }
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
        load::plugin("payment/wechat/Config");
        $config = new WxPayConfig($order['payment']);
        load::plugin("payment/wechat/type/WxPay.Check");
        $WxPayCheck = new WxPayCheck();
        $result     = $WxPayCheck->Check($config, $order['order']);
        if ($result['result_code'] == "SUCCESS" && $result['trade_state'] == "SUCCESS") {
            return ["code" => 1, "msg" => "订单已支付", "order_no" => $result['out_trade_no']];
        } else {
            return ["code" => 0, "msg" => "订单未支付：{$result['trade_state']}"];
        }
    }
    /**
     * [repay 必要 退款接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function repay($order)
    {
        $order = self::cfg($order);
        unset($order['payment']['notify_url']);
        load::plugin("payment/wechat/Config");
        $config = new WxPayConfig($order['payment']);
        load::plugin("payment/wechat/type/WxPay.Repay");
        $WxPayRepay = new WxPayRepay();
        $result     = $WxPayRepay->Order($config, $order['order']);
        if ($result['result_code'] == "SUCCESS") {
            return ["code" => 1, "msg" => "退款成功", "order_no" => $result['out_trade_no']];
        } else {
            return ["code" => 0, "msg" => "退款失败：{$result['err_code_des']}"];
        }
    }
    /**
     * [notify 必要 支付后回调通知]
     * @return [type] [description]
     */
    public static function notify()
    {
        load::plugin("payment/wechat/type/WxPay.Notify");
        $WxPayNotify = new WxPayNotify();
        return $WxPayNotify->check();
    }
    /**
     * [payto 非必要 转账给个人接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function payto($order)
    {
        $order = self::cfg($order);
        unset($order['payment']['notify_url']);
        load::plugin("payment/wechat/Config");
        $config = new WxPayConfig($order['payment']);
        if ($config->$get['paytotype'] != "1") {
            load::plugin("payment/wechat/type/WxPay.To");
            $order_no = "mch_billno";
        } else {
            load::plugin("payment/wechat/type/WxPay.ToB");
            $order_no = "partner_trade_no";
        }
        $WxPayTo = new WxPayTo();
        $result  = $WxPayTo->Order($config, $order['order']);
        if ($result['result_code'] == "SUCCESS") {
            return ["code" => 1, "msg" => "发送成功", "order_no" => $result[$order_no]];
        } else {
            return ["code" => 0, "msg" => "发送失败：{$result['err_code_des']}", "data" => $result];
        }
    }
}
