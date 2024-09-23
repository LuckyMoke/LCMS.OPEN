<?php
class AliPayOrder
{
    public $api, $cfg, $order;
    /**
     * @description: 接口初始化
     * @param array $init
     * @return {*}
     */
    public function __construct($init)
    {
        $this->api   = $init['config']['gatewayurl'];
        $this->cfg   = $init['config'];
        $this->order = $init['order'];
    }
    /**
     * @description: H5、PC下单
     * @param string $paytype
     * @return {*}
     */
    public function Jsapi($paytype = "h5")
    {
        if ($paytype === "pc") {
            $method       = "alipay.trade.page.pay";
            $product_code = "FAST_INSTANT_TRADE_PAY";
        } else {
            $method       = "alipay.trade.wap.pay";
            $product_code = "QUICK_WAP_WAY";
        }
        $input = [
            'method'      => $method,
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'notify_url'  => $this->cfg['notify_url'],
            'return_url'  => $this->order['return_url'],
            'biz_content' => json_encode([
                'out_trade_no' => $this->order['order_no'],
                'product_code' => $product_code,
                'total_amount' => $this->order['pay'],
                'subject'      => $this->order['body'],
                'goods_type'   => "1",
            ] + ($this->order['fenqi'] ?: [])),
        ];
        $input = AliPayApi::Sign($this->cfg, $input);
        foreach ($input as $key => $val) {
            $arr[$key] = str_replace("'", "&apos;", $val);
        }
        return [
            "url"  => $this->api . "?charset=" . $this->cfg['charset'],
            "data" => $arr ?: [],
        ];
    }
    /**
     * @description: 二维码下单
     * @param {*}
     * @return array
     */
    public function Qr()
    {
        $input = [
            'method'      => 'alipay.trade.precreate',
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'notify_url'  => $this->cfg['notify_url'],
            'biz_content' => json_encode([
                'out_trade_no'    => $this->order['order_no'],
                'total_amount'    => $this->order['pay'],
                'subject'         => $this->order['body'],
                'goods_type'      => "1",
                'timeout_express' => $this->cfg['timeout_express'],
            ] + ($this->order['fenqi'] ?: [])),
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => $this->api,
            "data"    => $input,
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            ],
        ]), true);
        if ($result && $result['alipay_trade_precreate_response']) {
            return $result['alipay_trade_precreate_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
    /**
     * @description: App下单
     * @param {*}
     * @return string
     */
    public function App()
    {
        $input = [
            'method'      => 'alipay.trade.app.pay',
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'notify_url'  => $this->cfg['notify_url'],
            'biz_content' => json_encode([
                'out_trade_no' => $this->order['order_no'],
                'product_code' => "QUICK_MSECURITY_PAY",
                'total_amount' => $this->order['pay'],
                'subject'      => $this->order['body'],
                'goods_type'   => "1",
            ] + ($this->order['fenqi'] ?: [])),
        ];
        $input = AliPayApi::Sign($this->cfg, $input);
        foreach ($input as $key => $val) {
            $arr[] = $key . "=" . urlencode($val);
        }
        return implode("&", $arr);
    }
    /**
     * @description: 检查订单状态
     * @param {*}
     * @return array
     */
    public function Check()
    {
        global $_L;
        $input = [
            'app_id'      => $this->cfg['appid'],
            'method'      => 'alipay.trade.query',
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'biz_content' => json_encode([
                "out_trade_no"  => $this->order['order_no'],
                "refund_amount" => $this->order['pay'],
            ]),
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => $this->api,
            "data"    => $input,
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            ],
        ]), true);
        if ($result && $result['alipay_trade_query_response']) {
            return $result['alipay_trade_query_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
    /**
     * @description: 退款
     * @param {*}
     * @return array
     */
    public function Repay()
    {
        $input = [
            'method'      => 'alipay.trade.refund',
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'biz_content' => json_encode([
                "out_trade_no"  => $this->order['order_no'],
                "refund_amount" => $this->order['pay'],
            ]),
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::request([
            "type"    => "POST",
            "url"     => $this->api,
            "data"    => $input,
            "build"   => true,
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            ],
        ]), true);
        if ($result && $result['alipay_trade_refund_response']) {
            return $result['alipay_trade_refund_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
}
