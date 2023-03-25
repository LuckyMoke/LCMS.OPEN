<?php
class AliPayTo
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
     * @description: 企业单笔转账到支付宝
     * @param {*}
     * @return array
     */
    public function Pay()
    {
        $input = [
            'method'      => 'alipay.fund.trans.toaccount.transfer',
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'biz_content' => json_encode([
                'out_biz_no'      => $this->order['order_no'],
                "payee_type"      => "ALIPAY_LOGONID",
                "payee_account"   => $this->order['account'],
                'amount'          => $this->order['pay'],
                'payee_real_name' => $this->order['name'],
                'remark'          => $this->order['info'],
            ]),
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::post($this->api, $input, true, [
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        ]), true);
        if ($result && $result['alipay_trade_refund_response']) {
            return $result['alipay_trade_refund_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
    /**
     * @description: 转账结果查询
     * @param {*}
     * @return array
     */
    public function Check()
    {
        $input = [
            'method'      => 'alipay.fund.trans.order.query',
            'app_id'      => $this->cfg['appid'],
            'format'      => $this->cfg['format'],
            'charset'     => $this->cfg['charset'],
            'sign_type'   => $this->cfg['sign_type'],
            'timestamp'   => $this->cfg['timestamp'],
            'version'     => $this->cfg['version'],
            'biz_content' => json_encode([
                'out_biz_no' => $this->order['order_no'],
            ]),
        ];
        $input  = AliPayApi::Sign($this->cfg, $input);
        $result = json_decode(HTTP::post($this->api, $input, true, [
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        ]), true);
        if ($result && $result['alipay_trade_refund_response']) {
            return $result['alipay_trade_refund_response'];
        } else {
            LCMS::X(401, "请求失败");
        }
    }
}
