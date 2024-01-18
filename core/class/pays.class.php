<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-01-10 20:00:32
 * @Description:下单支付操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PAYS
{
    /**
     * @description: 查询订单信息
     * @param string $order_no
     * @return array
     */
    public static function order_info($order_no = "")
    {
        return $order_no ? sql_get([
            "table" => "order",
            "where" => "order_no = :order_no",
            "bind"  => [
                ":order_no" => $order_no,
            ],
        ]) : [];
    }
    /**
     * @description: 获取支付信息
     * @param int $payid 支付ID
     * @return array
     */
    public static function payment_info($payid)
    {
        global $_L;
        $payinfo = LCMS::form([
            "table" => "payment",
            "do"    => "get",
            "id"    => $payid,
        ]);
        if ($payinfo) {
            $payinfo = array_merge([
                "id"      => $payinfo['id'],
                "payment" => $payinfo['payment'],
            ], $payinfo[$payinfo['payment']]);
        }
        if ($payinfo['agent'] > 0) {
            $agent = LCMS::form([
                "table" => "payment",
                "do"    => "get",
                "id"    => $payinfo['agent'],
            ]);
            if ($agent) {
                $payinfo['payment'] = $agent['payment'];
                $payinfo['agent']   = $agent[$agent['payment']];
            }
        }
        return $payinfo ?: [];
    }
    /**
     * @description: 获取支付方式列表
     * @param string $payment
     * @return array
     */
    public static function payment_list($payment = "")
    {
        global $_L;
        $list = $payment ? sql_getall([
            "table" => "payment",
            "where" => "payment = :payment AND lcms = :lcms",
            "order" => "id DESC",
            "bind"  => [
                ":payment" => $payment,
                ":lcms"    => $_L['ROOTID'],
            ],
        ]) : sql_getall([
            "table" => "payment",
            "where" => "lcms = :lcms",
            "order" => "id DESC",
            "bind"  => [
                ":lcms" => $_L['ROOTID'],
            ],
        ]);
        foreach ($list as $val) {
            $result[] = [
                "title" => "{$val['title']} - {$val['payment']} - ID:{$val['id']}",
                "value" => $val['id'],
            ];
        }
        return $result ?: [];
    }
    /**
     * @description: 获取支付方式配置文件
     * @return array
     */
    public static function payment_config()
    {
        global $_L;
        $dir     = PATH_CORE_PLUGIN . "payment/";
        $payment = traversal_one($dir)['dir'];
        if (is_array($payment)) {
            foreach ($payment as $val) {
                $json = "{$dir}{$val}/config.json";
                if (is_file($json)) {
                    $config[$val] = json_decode(file_get_contents($json), true);
                }
            }
        }
        return $config ?: [];
    }
    /**
     * @description: 下单、更新订单操作
     * @param array $order
     * @return array
     */
    public static function order($order = [])
    {
        if ($order['order_no']) {
            //更新订单
            switch ($order['status']) {
                case 2:
                    $order['repaytime'] = datenow();
                    break;
                case 1:
                    $order['paytime'] = datenow();
                    break;
            }
            sql_update([
                "table" => "order",
                "data"  => $order,
                "where" => "order_no = :order_no",
                "bind"  => [
                    ":order_no" => $order['order_no'],
                ],
            ]);
        } elseif ($order['payid'] && !$order['payment']) {
            //创建订单
            $payinfo = self::payment_info($order['payid']);
            $order   = array_merge($order, [
                "order_no" => date("YmdHis") . microseconds() . randstr(2),
                "payment"  => $payinfo['payment'],
                "addtime"  => datenow(),
            ]);
            sql_insert([
                "table" => "order",
                "data"  => $order,
            ]);
        }
        return $order['order_no'] ? self::order_info($order['order_no']) : [];
    }
    /**
     * @description: 获取支付页面链接
     * @param array $para
     * @return string
     */
    public static function get($para = [])
    {
        global $_L;
        $order = $para['order_no'] ? self::order_info($para['order_no']) : self::order([
            "body"    => $para['body'],
            "pay"     => $para['pay'],
            "paytype" => $para['paytype'],
            "payid"   => $para['payid'],
        ]);
        if ($order) {
            $paycode = urlsafe_base64_encode(gzcompress(json_encode_ex([
                "order_no"     => $order['order_no'],
                "order_no_own" => $para['order_no_own'],
                "payid"        => $order['payid'],
                "parameter"    => $para['parameter'],
                "goback"       => $para['goback'],
            ])));
            if ($para['parameter']['huabei'] > 0) {
                $url = "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=system&c=pay&paycode={$paycode}";
            } else {
                LOAD::sys_class('shortlink');
                $url = SHORTLINK::create([
                    "url"  => "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=system&c=pay",
                    "time" => 900,
                    "data" => [
                        "paycode" => $paycode,
                    ],
                ]);
            }
            return $url;
        }
    }
    /**
     * @description: 发起支付操作
     * @param array $paycode
     * @param array|string $openid 小程序支付需传入
     * @return {*}
     */
    public static function pay($paycode, $openid = "")
    {
        $payinfo = self::payment_info($paycode['payid']);
        $payinfo || LCMS::X(404, "未找到支付配置信息");
        $order = $paycode['order_no'] ? self::order([
            "order_no" => $paycode['order_no'],
            "payment"  => $payinfo['payment'],
        ]) : LCMS::X(404, "未找到订单信息");
        if ($order['status'] == 0) {
            if ($openid) {
                $order['openid'] = $openid;
                if (is_array($openid)) {
                    $payinfo = array_merge($payinfo, [
                        "appid"     => $openid['appid'] ?: $payinfo['appid'],
                        "appsecret" => $openid['appsecret'] ?: $payinfo['appsecret'],
                    ]);
                    $order['openid'] = $openid['openid'];
                }
            }
            load::plugin("payment/{$order['payment']}/AutoPay");
            $order = $paycode['parameter'] ? array_merge($order, $paycode['parameter']) : $order;
            return AutoPay::order([
                "payinfo" => $payinfo,
                "order"   => $order,
                "other"   => [
                    "order_no_own" => $paycode['order_no_own'],
                    "return_url"   => $paycode['goback'],
                ],
            ]);
        } else {
            goheader($paycode['goback']);
        }
    }
    /**
     * @description: 检查订单状态
     * @param string $order_no
     * @return array array
     */
    public static function check($order_no)
    {
        $order   = self::order_info($order_no);
        $payinfo = self::payment_info($order['payid']);
        if ($order && $payinfo) {
            if ($order['status'] == 1) {
                return ["code" => 1, "msg" => "订单已支付"];
            }
            load::plugin("payment/{$order['payment']}/AutoPay");
            $result = AutoPay::check([
                "payinfo" => $payinfo,
                "order"   => $order,
            ]);
            if ($result['code'] == 1 && $result['order_no']) {
                self::order([
                    "order_no" => $result['order_no'],
                    "paytime"  => datenow(),
                    "status"   => 1,
                ]);
            }
            return $result;
        }
        return ["code" => 0, "msg" => "未找到订单信息"];
    }
    /**
     * @description: 退款操作
     * @param string $order_no
     * @return array
     */
    public static function repay($order_no)
    {
        $order   = self::order_info($order_no);
        $payinfo = self::payment_info($order['payid']);
        if ($order && $payinfo && $order['status'] == 1) {
            load::plugin("payment/{$order['payment']}/AutoPay");
            $result = AutoPay::repay([
                "payinfo" => $payinfo,
                "order"   => $order,
            ]);
            if ($result['code'] == 1 && $result['order_no']) {
                self::order([
                    "order_no"  => $result['order_no'],
                    "repaytime" => datenow(),
                    "status"    => 2,
                ]);
            }
            return $result;
        }
        return ["code" => 0, "msg" => "退款失败：未支付或已退款"];
    }
    /**
     * @description: 支付结果通知
     * @param string $payment
     * @return {*}
     */
    public static function notify($payment)
    {
        load::plugin("payment/{$payment}/AutoPay");
        return AutoPay::notify();
    }
    /**
     * @description: 转账给用户
     * @param int $payid 支付ID
     * @param array $order 不同支付方式有不同的参数
     * @return array
     */
    public static function payto($payid, $order)
    {
        $payinfo = PAYS::payment_info($payid);
        load::plugin("payment/{$payinfo['payment']}/AutoPay");
        return AutoPay::payto([
            "payinfo" => $payinfo,
            "order"   => $order,
        ]);
    }
    /**
     * @description: 查询转账状态
     * @param int $payid 支付ID
     * @param array $order 不同支付方式有不同的参数
     * @return array
     */
    public static function payto_check($payid, $order)
    {
        $payinfo = PAYS::payment_info($payid);
        load::plugin("payment/{$payinfo['payment']}/AutoPay");
        return AutoPay::payto_check([
            "payinfo" => $payinfo,
            "order"   => $order,
        ]);
    }
}
