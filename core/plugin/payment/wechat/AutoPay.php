<?php
require_once "libs/WxPay.Config.php";
class AutoPay
{
    static $payname = "wechat";
    static $tpl     = PATH_CORE_PLUGIN . "payment/wechat/tpl/";
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
        $config = new WxPayConfig($order['payment']);
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
        $init  = self::init($order);
        $order = $init['order'];
        $WxPay = new WxPayOrder($init);
        switch ($order['paytype']) {
            case 'jsapi':
                $result = json_encode($WxPay->Jsapi());
                load::plugin('WeChat/OA');
                $WX = new OA([
                    "appid"     => $init['config']['appid'],
                    "appsecret" => $init['config']['appsecret'],
                    "thirdapi"  => $init['config']['thirdapi'],
                ]);
                $signpackage = $WX->signpackage();
                require LCMS::template(self::$tpl . "jsapi");
                break;
            case 'h5':
                $result = $WxPay->H5();
                goheader($result['h5_url']);
                break;
            case 'mini':
                return $WxPay->Jsapi($order['openid']);
                break;
            case 'pc':
                $result = $WxPay->Pc();
                $qrcode = urlencode($result['code_url']);
                require LCMS::template(self::$tpl . "pc");
                break;
        }
    }
    /**
     * @description: 必须 查询订单状态接口
     * @param array $order
     * @return array
     */
    public static function check($order)
    {
        $WxPay  = new WxPayOrder(self::init($order));
        $result = $WxPay->Check();
        if ($result['trade_state'] == "SUCCESS") {
            return [
                "code"     => 1,
                "msg"      => "订单已支付",
                "order_no" => $result['out_trade_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "{$result['trade_state_desc']}：{$result['trade_state']}",
                "data" => $result,
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
        $WxPay  = new WxPayOrder(self::init($order));
        $result = $WxPay->Repay();
        if ($result['status'] == "SUCCESS" || $result['status'] == "PROCESSING") {
            return [
                "code"     => 1,
                "msg"      => "退款成功",
                "order_no" => $result['out_trade_no'],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "退款失败",
                "data" => $result,
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
        $Notify = new WxPayNotify();
        return $Notify->Check(self::$payname);
    }
    /**
     * @description: 可选 转账给个人接口
     * @param array $order
     * @return array
     */
    public static function payto($order)
    {
        require_once "libs/WxPay.To.php";
        $init    = self::init($order);
        $WxPayTo = new WxPayTo($init);
        if ($init['config']['paytotype'] != "1") {
            // 微信付款到红包
            $result = $WxPayTo->couPon();
            $type   = "mch_billno";
        } else {
            // 微信付款到零钱
            $result = $WxPayTo->Pay();
            $type   = "partner_trade_no";
        }
        if ($result['result_code'] == "SUCCESS") {
            return [
                "code"     => 1,
                "msg"      => "发送成功",
                "order_no" => $result[$type],
            ];
        } else {
            return [
                "code" => 0,
                "msg"  => "发送失败：{$result['err_code_des']}",
                "data" => $result,
            ];
        }
    }
}
