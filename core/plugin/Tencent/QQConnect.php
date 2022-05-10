<?php
class QQConnect
{
    /**
     * @description: 初始化配置
     * @param array $config
     * @return {*}
     */
    public function __construct($config = [])
    {
        global $_L, $CFG, $SID;
        $CFG = array_merge([
            "display" => "",
        ], $config);
        $SID = "QQ" . strtoupper(substr(md5($CFG['appid']), 8, 16));
    }
    /**
     * @description: 无缓存跳转
     * @param string $url
     * @return {*}
     */
    public function headerNocache($url)
    {
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cahe, must-revalidate');
        header('Cache-Control: post-chedk=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("HTTP/1.1 302 Temporarily Moved");
        header("Location: $url");
        exit;
    }
    /**
     * @description: 用户授权
     * @param string $redirect_uri
     * @return {*}
     */
    public function oauth2($redirect_uri)
    {
        global $_L, $CFG, $SID;
        $url = "https://graph.qq.com/oauth2.0/authorize?" . http_build_query([
            "response_type" => "token",
            "client_id"     => $CFG['appid'],
            "redirect_uri"  => $redirect_uri,
            "scope"         => "get_user_info",
            "state"         => ssl_encode(time() + 120, "QQConnect"),
            "display"       => $CFG['display'],
        ]);
        $this->headerNocache($url);
    }
    /**
     * @description: ajax从服务端获取openid
     * @param {*}
     * @return {*}
     */
    public function getOpenid($token, $state)
    {
        global $_L, $CFG, $SID;
        $state = ssl_decode($state, "QQConnect");
        if ($state > time()) {
            $url    = "https://graph.qq.com/oauth2.0/me?";
            $result = HTTP::get($url . http_build_query([
                "access_token" => $token,
                "fmt"          => "json",
            ]));
            $result = json_decode($result, true);
            if ($result['openid']) {
                SESSION::set("{$SID}openid", $result['openid']);
                return $result['openid'];
            }
        }
    }
    /**
     * @description: 获取OPENID
     * @return string
     */
    public function openid()
    {
        global $_L, $CFG, $SID;
        if ($_L['form']['access_token']) {
            $openid = $this->getOpenid($_L['form']['access_token'], $_L['form']['state']);
            if ($openid) {
                okinfo(url_clear($_L['url']['now'], "access_token|state"));
            }
        } else {
            $openid = SESSION::get("{$SID}openid");
            if ($openid) {
                return $openid;
            }
            $this->oauth2("{$_L['config']['web']['domain_api']}core/plugin/Tencent/tpl/qqlogin.html?ver={$_L["config"]["ver"]}goback=" . urlencode($_L['url']['now']));
        }
    }
}
