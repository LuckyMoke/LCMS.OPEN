<?php
class WxPayTo
{
    public $api, $cfg, $order;
    /**
     * @description: 接口初始化
     * @param array $init
     * @return {*}
     */
    public function __construct($init)
    {
        $this->api   = "https://api.mch.weixin.qq.com";
        $this->cfg   = $init['config'];
        $this->order = $init['order'];
    }
    /**
     * @description: 商家转账到零钱
     * @param {*}
     * @return {*}
     */
    public function Pay()
    {
        $url    = "/v3/transfer/batches";
        $result = WxPayApi::Request("POST", $this->api . $url, $this->cfg, json_encode(array_merge([
            "appid" => $this->cfg['appid'],
        ], $this->order)));
        if ($result['code'] || !$result['out_batch_no']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
    /**
     * @description: 转账结果查询
     * @param {*}
     * @return array
     */
    public function Check()
    {
        $url = "/v3/transfer/batches/batch-id/{$this->order['out_batch_no']}?";
        $url .= implode("&", [
            "need_query_detail=" . ($this->order['out_batch_no'] ? "true" : "false"),
            "offset=" . ($this->order['offset'] ?: "0"),
            "limit=" . ($this->order['limit'] ?: "20"),
            "detail_status=" . ($this->order['detail_status'] ?: "ALL"),
        ]);
        $result = WxPayApi::Request("GET", $url, $this->cfg);
        if ($result['code'] || !$result['transfer_batch']) {
            LCMS::X($result['code'], $result['message']);
        } else {
            return $result;
        }
    }
}
