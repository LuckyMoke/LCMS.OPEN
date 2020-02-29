<?php
// 创建订单
class CodePayCreat
{
    public function Order($config, $order)
    {
        global $_L;
        $url   = "https://codepay.fateqq.com/creat_order/?";
        $input = [
            "id"         => $config->$get['appid'],
            "page"       => $config->$get['page'],
            "pay_id"     => $order['order_no'], // 订单号
            "type"       => $order['paytype'] == "wechat" ? "3" : ($order['paytype'] == "alipay" ? "1" : "2"), // 支付方式
            "price"      => $order['pay'],
            "notify_url" => $config->$get['notify_url'],
            'return_url' => $config->$get['return_url'],
        ];
        if ($config->$get['appid'] && $config->$get['appsecret']) {
            $query = $this->buildRequestJson($config, $input);
            if ($config->$get['page'] == 4) {
                $result = json_decode(http::get($url . $query), true);
                if ($result['status'] == "0") {
                    return ["code" => "1", "msg" => "success", "data" => $result];
                }
            } else {
                return ["code" => "1", "msg" => "success", "url" => $url . $query];
            }
        } else {
            return ["code" => "0", "msg" => "缺少必要参数"];
        }
    }
    protected function buildRequestJson($config, $input)
    {
        ksort($input);
        reset($input);
        $sign = '';
        $urls = '';
        foreach ($input as $key => $val) {
            if ($val == '' || $key == 'sign') {
                continue;
            }
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val";
            $urls .= "$key=" . urlencode($val);
        }
        $query = $urls . '&sign=' . md5($sign . $config->$get['appsecret']);
        return $query;
    }
}
