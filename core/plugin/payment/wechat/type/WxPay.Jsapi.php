<?php
load::plugin("payment/wechat/type/lib/WxPay.Api");
// JSAPI支付
class WxPayJsapi
{
    public function Order($config, $order)
    {
        $url    = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $openid = $this->getOpenid($config);
        if ($openid['openid']) {
            $input = [
                "appid"            => $config->get['appid'],
                "mch_id"           => $config->get['mch_id'],
                "body"             => $order['body'],
                "out_trade_no"     => $order['order_no'],
                "total_fee"        => $order['pay'] * 100,
                "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
                "notify_url"       => $config->get['notify_url'],
                "trade_type"       => "JSAPI",
                "openid"           => $openid['openid'],
                "sign_type"        => $config->get['sign_type'],
                "nonce_str"        => WxPayApi::NonceStr(),
            ];
        }
        $input['sign'] = WxPayApi::Sign($config, $input);
        $xml           = WxPayApi::ToXml($input);
        $result        = $this->GetJsApiParameters($config, WxPayApi::FromXml(WxPayApi::postXmlCurl($config, $xml, $url, false, 6)));
        return $result;
    }
    /**
     * [GetJsApiParameters JSAPI支付参数生成]
     * @param [type] $config [description]
     * @param [type] $result [description]
     */
    public function GetJsApiParameters($config, $result)
    {
        if ($result['return_code'] == "FAIL") {
            LCMS::X($result['return_code'], $result['return_msg']);
        } elseif (!$result['appid'] || !$result['prepay_id']) {
            LCMS::X($result['err_code'], $result['err_code_des']);
        } else {
            $jsapi = [
                "appId"     => $result["appid"],
                "timeStamp" => strval(time()),
                "nonceStr"  => WxPayApi::NonceStr(),
                "package"   => "prepay_id={$result['prepay_id']}",
                "signType"  => $config->get['sign_type'],
            ];
            $jsapi['paySign'] = WxPayApi::Sign($config, $jsapi);
            return json_encode($jsapi);
        }
    }
    /**
     * [getOpenid 获取用户OPENID信息]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public function getOpenid($config)
    {
        global $_L;
        $openid = session::get("WeChat_snsapi_base_" . $config->get['mch_id']);
        if ($openid['openid']) {
            return $openid;
        } else {
            if ($config->get['oauth']) {
                if ($_L['form']['wechatpayoauth']) {
                    session::set("WeChat_snsapi_base_" . $config->get['mch_id'], ["openid" => $_L['form']['wechatpayoauth']]);
                    goheader(url_clear($_L['url']['now'], "code|state"));
                } else {
                    goheader($config->get['oauth'] . urlencode($_L['url']['now']) . "&key=wechatpayoauth");
                }
            } else {
                if (!isset($_L['form']['code'])) {
                    $query = http_build_query([
                        "appid"         => $config->get['appid'],
                        "redirect_uri"  => $_L['url']['now'],
                        "response_type" => "code",
                        "scope"         => "snsapi_base",
                    ]);
                    $this->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
                    exit();
                } else {
                    $openid = $this->getOpenidFromMp($config, $_L['form']['code']);
                    if ($openid['openid']) {
                        session::set("WeChat_snsapi_base_" . $config->get['mch_id'], $openid);
                        goheader(url_clear($_L['url']['now'], "code|state"));
                    }
                }
            }
        }
    }
    private function getOpenidFromMp($config, $code)
    {
        $query = http_build_query([
            "appid"      => $config->get['appid'],
            "secret"     => $config->get['appsecret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        ]);
        return json_decode(http::get("https://api.weixin.qq.com/sns/oauth2/access_token?{$query}"), true);
    }
    /**
     * [header_nocache 无缓存跳转]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    private function header_nocache($url)
    {
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cahe, must-revalidate');
        header('Cache-Control: post-chedk=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url");
    }
}
