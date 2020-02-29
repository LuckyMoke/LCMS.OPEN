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
        $order['payment']['notify_url'] = "{$_L['url']['site']}core/plugin/payment/codepay/notify.php";
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
        load::plugin("payment/codepay/Config");
        $config = new CodePayConfig($order['payment']);
        load::plugin("payment/codepay/type/CodePay.Creat");
        $CodePayCreat = new CodePayCreat();
        $result       = $CodePayCreat->Order($config, $order['order']);
        if ($result['url']) {
            goheader($result['url']);
        } elseif ($result['data']['qrcode']) {
            $result = $result['data'];
            if (is_mobile()) {
                require LCMS::template(PATH_CORE_PLUGIN . "payment/codepay/tpl/h5");
            } else {
                require LCMS::template(PATH_CORE_PLUGIN . "payment/codepay/tpl/pc");
            }
        } else {
            LCMS::X($result['code'], $result['msg']);
        }
    }
    /**
     * [repay 必要 退款接口]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public static function repay($order)
    {
        return ["code" => 0, "msg" => "退款失败：码支付无退款接口"];
    }
    /**
     * [notify 必要 支付后回调通知]
     * @return [type] [description]
     */
    public static function notify()
    {
        load::plugin("payment/codepay/type/CodePay.Notify");
        $CodePayNotify = new CodePayNotify();
        return $CodePayNotify->check();
    }
}
