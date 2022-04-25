<?php
require_once "libs/AliPay.Config.php";
class AutoPay
{
    static $payname = "alipay";
    static $tpl     = PATH_CORE_PLUGIN . "payment/alipay/tpl/";
    /**
     * @description: 必须 支付参数配置
     * @param array $order
     * @return array
     */
    public static function init($order)
    {
        global $_L;
        $order['payment'] = array_merge($order['payment'], [
            "notify_url" => "{$_L['url']['site']}paynotify/" . self::$payname . "/{$order['order']['order_no']}",
        ]);
        $config = new AliPayConfig($order['payment']);
        return [
            "config" => $config->get,
            "order"  => array_merge($order['order'], $order['other'] ?: []),
        ];
    }
    /**
     * @description: 必须 下单方法
     * @param array $order
     * @return {*}
     */
    public static function order($order)
    {
        global $_L;
        $init   = self::init($order);
        $order  = $init['order'];
        $AliPay = new AliPayOrder($init);
        $huabei = $order['huabei'];
        $huabei = $huabei ? explode("|", $huabei) : [];
        $fenqi  = $_L['form']['fenqi'] ?: $huabei[1];
        $seller = $huabei > 12 ? "0" : "100";
        if ($fenqi > 6) {
            // 分12期
            $order['fenqi'] = [
                "extend_params" => [
                    "hb_fq_num"            => "12",
                    "hb_fq_seller_percent" => $seller,
                ],
            ];
        } elseif ($fenqi > 3) {
            // 分6期
            $order['fenqi'] = [
                "extend_params" => [
                    "hb_fq_num"            => "6",
                    "hb_fq_seller_percent" => $seller,
                ],
            ];
        } elseif ($fenqi > 0) {
            // 分3期
            $order['fenqi'] = [
                "extend_params" => [
                    "hb_fq_num"            => "3",
                    "hb_fq_seller_percent" => $seller,
                ],
            ];
        }
        $huabei = $huabei[0];
        switch ($order['paytype']) {
            case 'h5':
            case 'jsapi':
                $result = $AliPay->Jsapi();
                $UA     = $_SERVER['HTTP_USER_AGENT'];
                if (strpos($UA, 'MicroMessenger') !== false || strpos($UA, 'QQ/') !== false) {
                    $openInBrowser = true;
                }
                require LCMS::template(self::$tpl . "h5");
                break;
            case 'qr':
                $result = $AliPay->Qr();
                if ($result['code'] == '10000') {
                    $qrcode = urlencode($result['qr_code']);
                    require LCMS::template(self::$tpl . "qr");
                } else {
                    LCMS::X(401, "创建订单失败：{$result['sub_msg']}");
                }
                break;
            case 'app':
                return $AliPay->App();
                break;
            case 'pc':
                $result = $AliPay->Jsapi("pc");
                require LCMS::template(self::$tpl . "pc");
                break;
        }
    }
    /**
     * @description: 必须 查询订单接口
     * @param array $order
     * @return arrat
     */
    public static function check($order)
    {
        $AliPay = new AliPayOrder(self::init($order));
        $result = $AliPay->Check();
        if ($result['code'] == "10000" && $result['trade_status'] === "TRADE_SUCCESS") {
            return [
                "code"     => 1,
                "msg"      => "订单已支付",
                "order_no" => $result['out_trade_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "订单未支付：{$result['sub_msg']}{$result['trade_status']}",
            ];
        }
    }
    /**
     * @description: 必须 退款接口
     * @param array $order
     * @return array
     */
    public static function repay($order)
    {
        $AliPay = new AliPayOrder(self::init($order));
        $result = $AliPay->Repay();
        if ($result['code'] == "10000") {
            return [
                "code"     => 1,
                "msg"      => "退款成功",
                "order_no" => $result['out_trade_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "退款失败：{$result['sub_msg']}",
            ];
        }
    }
    /**
     * @description: 必须 支付后回调通知
     * @param {*}
     * @return array
     */
    public static function notify()
    {
        $Notify = new AliPayNotify();
        return $Notify->Check(self::$payname);
    }
    /**
     * @description: 可选 转账给个人接口
     * @param array $order
     * @return array
     */
    public static function payto($order)
    {
        require_once "libs/AliPay.To.php";
        $AliPayTo = new AliPayTo(self::init($order));
        $result   = $AliPayTo->Pay();
        if ($result['code'] == "10000") {
            return [
                "code"     => 1,
                "msg"      => "转账成功",
                "order_no" => $result['out_biz_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "转账失败：{$result['sub_msg']}",
            ];
        }
    }
    /**
     * @description: 可选 转账结果查询
     * @param array $order
     * @return array
     */
    public static function payto_check($order)
    {
        require_once "libs/AliPay.To.php";
        $AliPayTo = new AliPayTo(self::init($order));
        $result   = $AliPayTo->Check();
        if ($result['code'] == "10000") {
            return [
                "code"     => 1,
                "msg"      => "转账成功",
                "order_no" => $result['out_biz_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "转账失败：{$result['sub_msg']}",
            ];
        }
    }
}
