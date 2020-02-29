<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class("webbase");
load::sys_class("pays");
class pay extends webbase
{
    protected $paycode;
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        PAYS::pay(json_decode(base64_decode($_L['form']['paycode']), true));
    }
    public function docheck()
    {
        global $_L;
        $order = PAYS::order_info($_L['form']['order_no']);
        if ($order && $order['status'] == "1") {
            ajaxout(1, "支付成功");
        } else {
            ajaxout(0, "待支付");
        }
    }
    public function donotify()
    {
        global $_L;
        $order = PAYS::notify(PLUGIN_PAYMENT);
        if ($order && $order['order_no']) {
            PAYS::order([
                "order_no" => $order['order_no'],
                "paytime"  => datenow(),
                "status"   => "1",
            ]);
        }
        echo "success";
    }
    public function dotest()
    {
        global $_L;
        // $paytoid = "3";
        // load::plugin("payment/wechat/AutoPay");
        // $result = AutoPay::payto([
        //     "payment" => PAYS::payment_info($paytoid),
        //     "order"   => [
        //         "order_no" => "TEST20190812001",
        //         "openid"   => "o2BSos4QdExrTlVixwxa9cKoqzsg",
        //         "pay"      => "0.30",
        //         "desc"     => "活动赠送 - 来自盘石科技",
        //     ],
        // ]);
        // dump($result);
    }
}
