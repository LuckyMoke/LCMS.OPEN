<?php
class OA
{
    public $cfg = array();
    /**
     * [config 获取基本appid和appsecret]
     * @return [type] [description]
     */
    public function config()
    {
        global $_L;
        if (!$this->$cfg['appid'] || !$this->$cfg['appsecret']) {
            $config     = LCMS::config(array("name" => "wechat"));
            $this->$cfg = array(
                "appid"     => $config['appid'],
                "appsecret" => $config['appsecret'],
            );
        };
    }
    /**
     * [cache 数据缓存读取与保存]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function cache($type = "get")
    {
        $this->config();
        if ($this->$cfg['appid'] && $this->$cfg['appsecret']) {
            $cachename = md5($this->$cfg['appid'] . $this->$cfg['appsecret']);
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cfg(["name" => $cachename, "data" => $this->$cfg]);
                break;
            default:
                $cache      = LCMS::cfg(["name" => $cachename]);
                $this->$cfg = is_array($cache) ? array_merge($this->$cfg, $cache) : $this->$cfg;
                break;
        }
    }
    /**
     * [access_token 全局access_token 获取有次数限制]
     * @return [type] [description]
     */
    public function access_token()
    {
        $this->cache();
        if (!$this->$cfg['access_token']['token'] || $this->$cfg['access_token']['expires'] < time()) {
            $query = http_build_query(array(
                "appid"      => $this->$cfg['appid'],
                "secret"     => $this->$cfg['appsecret'],
                "grant_type" => "client_credential",
            ));
            $token = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/token?{$query}"), true);
            if ($token['errcode']) {
                return $token;
            } else {
                $this->$cfg['access_token'] = array(
                    "token"   => $token['access_token'],
                    "expires" => time() + 3600,
                );
                $this->cache("save");
            }
        }
        return $this->$cfg['access_token']['token'];
    }
    /**
     * [getOpenidFromMp 使用code获取用户数据]
     * @param  [type] $code [code值]
     * @return [type]       [数组]
     */
    private function getOpenidFromMp($code)
    {
        $query = http_build_query(array(
            "appid"      => $this->$cfg['appid'],
            "secret"     => $this->$cfg['appsecret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        ));
        return json_decode(http::get("https://api.weixin.qq.com/sns/oauth2/access_token?{$query}"), true);
    }
    /**
     * [openid 微信登陆获取openid]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function openid($type = false)
    {
        global $_L;
        $this->cache();
        $scope  = $type ? "snsapi_userinfo" : "snsapi_base";
        $openid = session::get($this->$cfg['appid'] . "_WeChat_" . $scope);
        if ($openid['openid'] && $scope == "snsapi_base") {
            return $openid;
        } elseif ($openid['openid'] && $scope == "snsapi_userinfo" && $openid['expires_time'] > time()) {
            return $openid;
        } else {
            if ($_L['form']['wechatoauthopenid']) {
                // 借权获取
                $userinfo = $this->user(["openid" => $_L['form']['wechatoauthopenid']]);
                if ($userinfo['wechat']['openid']) {
                    session::set($this->$cfg['appid'] . "_WeChat_snsapi_base", ["openid" => $userinfo['wechat']['openid']]);
                    okinfo(url_clear($_L['url']['now'], "wechatoauthopenid"));
                }
            } else {
                if ($_L['config']['web']['domain'] && $_L['config']['web']['domain'] != HTTP_HOST) {
                    $goback = urlencode($_L['url']['now']);
                    okinfo("{$_L['url']['sys']['own']}n=wechat&c=index&a=oauth&scope={$scope}&goback={$goback}");
                    exit();
                }
                // 用户授权登陆，获取code
                $code = $_L['form']['code'];
                if (!isset($code)) {
                    $query = http_build_query(array(
                        "appid"         => $this->$cfg['appid'],
                        "redirect_uri"  => $_L['url']['now'],
                        "response_type" => "code",
                        "scope"         => $scope,
                    ));
                    $this->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
                    exit();
                } else {
                    // 使用code获取用户数据
                    $openid = $this->getOpenidFromMp($code);
                    if ($openid['openid']) {
                        $this->user(array("do" => "save", "openid" => $openid['openid'], "wechat" => array("openid" => $openid['openid'])));
                        $openid['expires_time'] = time() + 3600;
                        session::set($this->$cfg['appid'] . "_WeChat_" . $scope, $openid);
                        okinfo(url_clear($_L['url']['now'], "code|state"));
                    }
                }
            }
        }
    }
    /**
     * [userinfo 获取微信用户的详细信息]
     * @return [type] [数组]
     */
    public function userinfo($para = array())
    {
        global $_L;
        if ($para['type'] == "subscribe") {
            $this->access_token();
            $query = http_build_query(array(
                "access_token" => $this->$cfg['access_token']['token'],
                "openid"       => $para['openid'],
                "lang"         => "zh_CN",
            ));
            $userinfo = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/user/info?{$query}"), true);
            if ($userinfo && !$userinfo['errcode']) {
                $userinfo = $this->user(array("do" => "save", "openid" => $userinfo['openid'], "wechat" => $userinfo));
            }
        } else {
            $this->config();
            $userinfo = session::get($this->$cfg['appid'] . "_WeChat_userinfo");
            if (!$userinfo['wechat']['openid'] || $userinfo['errcode']) {
                if ($_L['form']['wechatoauthopenid']) {
                    // 借权获取
                    $userinfo = $this->user(["openid" => $_L['form']['wechatoauthopenid']]);
                    if ($userinfo['wechat']['openid']) {
                        session::set($this->$cfg['appid'] . "_WeChat_userinfo", $userinfo);
                        okinfo(url_clear($_L['url']['now'], "wechatoauthopenid"));
                        exit();
                    }
                } else {
                    $openid = $this->openid(true);
                    $query  = http_build_query(array(
                        "access_token" => $openid['access_token'],
                        "openid"       => $openid['openid'],
                        "lang"         => "zh_CN",
                    ));
                    $userinfo = json_decode(http::get("https://api.weixin.qq.com/sns/userinfo?{$query}"), true);
                    if ($userinfo && !$userinfo['errcode']) {
                        $userinfo['nickname'] = filterEmoji($userinfo['nickname']);
                        $userinfo             = $this->user(array("do" => "save", "openid" => $userinfo['openid'], "wechat" => $userinfo));
                        session::set($this->$cfg['appid'] . "_WeChat_userinfo", $userinfo);
                    }
                }
            }
        }
        return $userinfo;
    }
    /**
     * [usersave 用户数据的保存与读取]
     * @param  array  $para [description]
     * @return [type]       [description]
     */
    public function user($para = array())
    {
        global $_L;
        $wechat = sql_get(["open_wechat_user", "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
        if ($para['do'] == "save") {
            if ($wechat) {
                $form = [
                    "subscribe"       => $para['wechat']['subscribe'],
                    "nickname"        => $para['wechat']['nickname'],
                    "sex"             => $para['wechat']['sex'],
                    "city"            => $para['wechat']['city'],
                    "country"         => $para['wechat']['country'],
                    "province"        => $para['wechat']['province'],
                    "language"        => $para['wechat']['language'],
                    "headimgurl"      => $para['wechat']['headimgurl'],
                    "subscribe_time"  => $para['wechat']['subscribe_time'],
                    "unionid"         => $para['wechat']['unionid'],
                    "remark"          => $para['wechat']['remark'],
                    "groupid"         => $para['wechat']['groupid'],
                    "subscribe_scene" => $para['wechat']['subscribe_scene'],
                    "qr_scene"        => $para['wechat']['qr_scene'],
                    "qr_scene_str"    => $para['wechat']['qr_scene_str'],
                    "location"        => $para['wechat']['location'],
                    "activetime"      => $para['wechat']['activetime'],
                    "parameter"       => $para['wechat']['parameter'],
                ];
                foreach ($form as $key => $val) {
                    if ($val === false || $val === "" || $val === null) {
                        unset($form[$key]);
                    }
                }
                sql_update(["open_wechat_user", $form, "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
            } else {
                sql_insert(["open_wechat_user", [
                    "subscribe"       => $para['wechat']['subscribe'],
                    "openid"          => $para['wechat']['openid'],
                    "nickname"        => $para['wechat']['nickname'],
                    "sex"             => $para['wechat']['sex'],
                    "city"            => $para['wechat']['city'],
                    "country"         => $para['wechat']['country'],
                    "province"        => $para['wechat']['province'],
                    "language"        => $para['wechat']['language'],
                    "headimgurl"      => $para['wechat']['headimgurl'],
                    "subscribe_time"  => $para['wechat']['subscribe_time'],
                    "unionid"         => $para['wechat']['unionid'],
                    "remark"          => $para['wechat']['remark'],
                    "groupid"         => $para['wechat']['groupid'],
                    "subscribe_scene" => $para['wechat']['subscribe_scene'],
                    "qr_scene"        => $para['wechat']['qr_scene'],
                    "qr_scene_str"    => $para['wechat']['qr_scene_str'],
                    "location"        => $para['wechat']['location'],
                    "activetime"      => $para['wechat']['activetime'],
                    "parameter"       => $para['wechat']['parameter'],
                    "lcms"            => $_L['ROOTID'],
                ]]);
            }
        }
        if ($para['openid']) {
            $wechat = $wechat ? $wechat : sql_get(["open_wechat_user", "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
            if ($wechat) {
                $userinfo['wechat'] = $para['wechat'] ? array_merge($wechat, $para['wechat']) : $wechat;
            }
            if ($userinfo['wechat']['uid'] > "0") {
                $user = sql_get(["user", "id = '{$userinfo['wechat']['uid']}' AND lcms = '{$_L['ROOTID']}'"]);
                if ($user) {
                    $userinfo['user'] = $user;
                }
            }
            return $userinfo;
        } else {
            return false;
        }
    }
    /**
     * [jsapi_ticket 获取全局 jsapi_ticket]
     * @return [type] [字符串]
     */
    public function jsapi_ticket()
    {
        $this->cache();
        if (!$this->$cfg['jsapi_ticket']['ticket'] || $this->$cfg['jsapi_ticket']['expires'] < time()) {
            $this->access_token();
            $query = http_build_query(array(
                "access_token" => $this->$cfg['access_token']['token'],
                "type"         => "jsapi",
            ));
            $ticket = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?{$query}"), true);
            if ($ticket['errcode']) {
                return $ticket;
            } else {
                $this->$cfg['jsapi_ticket'] = array(
                    "ticket"  => $ticket['ticket'],
                    "expires" => time() + 7000,
                );
                $this->cache("save");
            }
        }
    }
    /**
     * [signpackage 获取前台JSSDK签名]
     * @return [type] [description]
     */
    public function signpackage($url = "")
    {
        global $_L;
        $this->jsapi_ticket();
        $url         = $url ? $url : $_L['url']['now'];
        $nonceStr    = randstr(16);
        $timestamp   = time();
        $query       = implode("&", ["jsapi_ticket=" . $this->$cfg['jsapi_ticket']['ticket'], "noncestr={$nonceStr}", "timestamp={$timestamp}", "url={$url}"]);
        $signPackage = array(
            "appId"     => $this->$cfg['appid'],
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => sha1($query),
            "rawString" => $query,
        );
        return $signPackage;
    }
    /**
     * [send_tpl 发送模板消息]
     * @param  [type] $para [参数参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277 微信开发文档]
     * @return [type]       [description]
     */
    public function send_tpl($para = array())
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->$cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [send_custom 发送客服消息]
     * @param  [type] $para [参数参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140547 微信开发文档]
     * @return [type]       [description]
     */
    public function send_custom($para = array())
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->$cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [send_once 一次性订阅消息]
     * @param  [type] $para [开发参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1500374289_66bvB]
     * @return [type]       [description]
     */
    public function send_once($para = array())
    {
        global $_L;
        $this->access_token();
        $reserved = session::get($this->$cfg['appid'] . "_WeChat_reserved");
        if (!$_L['form']['reserved']) {
            $reserved = randstr(32);
            session::set($this->$cfg['appid'] . "_WeChat_reserved", $reserved);
            $query = http_build_query(array(
                "action"       => "get_confirm",
                "appid"        => $this->$cfg['appid'],
                "scene"        => 1,
                "template_id"  => $para['template_id'],
                "redirect_url" => $para['redirect_url'],
                "reserved"     => $reserved,
            ));
            okinfo("https://mp.weixin.qq.com/mp/subscribemsg?{$query}#wechat_redirect");
        } elseif ($_L['form']['reserved'] == $reserved) {
            $result = http::post("https://api.weixin.qq.com/cgi-bin/message/template/subscribe?access_token={$this->$cfg['access_token']['token']}", json_encode_ex(array(
                "touser"      => $para['touser'],
                "template_id" => $para['template_id'],
                "miniprogram" => $para['miniprogram'],
                "scene"       => $_L['form']['scene'],
                "title"       => $para['title'],
                "data"        => $para['data'],
            )));
            return $result;
        } else {
            return false;
        }
    }
    /**
     * [menu 设置、获取公众号菜单]
     * @param  [type] $para [description]
     * @return [type]       [数组，errcode为0代表成功]
     */
    public function menu($para = array())
    {
        $this->access_token();
        if (is_array($para)) {
            $result = http::post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->$cfg['access_token']['token']}", json_encode_ex($para));
        } elseif ($para == "get") {
            $result = http::get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->$cfg['access_token']['token']}");
        }
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [material 素材上传]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function material($para = array())
    {
        $this->access_token();
        $file     = path_absolute($para['file']);
        $fileinfo = pathinfo($file);
        $size     = filesize($file);
        $mime     = array(
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'mp3'  => 'audio/mp3',
            'wma'  => 'audio/x-ms-wma',
        );
        if ($mime) {
            $fileinfo = array(
                'filename'     => $fileinfo['basename'],
                'content-type' => $mime[$fileinfo['extension']],
                'filelength'   => filesize($file),
            );
            clearstatcache();
            if ($para['temp']) {
                //临时素材
                $result = http::post("https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->$cfg['access_token']['token']}&type={$para['type']}", array(
                    "media"     => new CURLFile($file),
                    "form-data" => $fileinfo,
                ));
            } else {
                //永久素材
                $result = http::post("https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$this->$cfg['access_token']['token']}&type={$para['type']}", array(
                    "media"     => new CURLFile($file),
                    "form-data" => $fileinfo,
                ));
            }
            return json_decode($result, true);
        } else {
            return array("errcode" => 403, "errmsg" => "不支持的文件格式");
        }
    }
    /**
     * [reply 关键词操作]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function reply($para = array())
    {
        global $_L;
        switch ($para['do']) {
            case 'del':
                if ($para['name']) {
                    $words = sql_get(["open_wechat_reply_words", "name = '{$para['name']}' AND app = '" . L_NAME . "' AND lcms = '{$_L['ROOTID']}'"]);
                    sql_delete(["open_wechat_reply", "id = '{$words['rid']}'"]);
                    sql_delete(["open_wechat_reply_words", "rid = '{$words['rid']}'"]);
                    sql_delete(["open_wechat_reply_contents", "rid = '{$words['rid']}'"]);
                    return true;
                } else {
                    return false;
                }
                break;
            case 'delall':
                sql_delete(["open_wechat_reply", "app = '" . L_NAME . "' AND lcms = '{$_L['ROOTID']}'"]);
                sql_delete(["open_wechat_reply_words", "app = '" . L_NAME . "' AND lcms = '{$_L['ROOTID']}'"]);
                sql_delete(["open_wechat_reply_contents", "app = '" . L_NAME . "' AND lcms = '{$_L['ROOTID']}'"]);
                return true;
                break;
            default:
                if ($para['name'] && $para['class'] && $para['func']) {
                    $words = sql_get(["open_wechat_reply_words", "name = '{$para['name']}' AND app = '" . L_NAME . "' AND lcms = '{$_L['ROOTID']}'"]);
                    if ($words) {
                        sql_update(["open_wechat_reply_contents", [
                            "parameter" => arr2sql([
                                "open" => [
                                    "class" => $para['class'],
                                    "func"  => $para['func'],
                                ],
                            ]),
                        ], "rid = '{$words['rid']}'"]);
                        return $words['rid'];
                    } else {
                        $insert_id = sql_insert(["open_wechat_reply", [
                            "type"     => "2",
                            "app"      => L_NAME,
                            "order_no" => "999999",
                            "lcms"     => $_L['ROOTID'],
                        ]]);
                        if ($insert_id) {
                            sql_insert(["open_wechat_reply_words", [
                                "rid"  => $insert_id,
                                "name" => $para['name'],
                                "app"  => L_NAME,
                                "type" => "1",
                                "lcms" => $_L['ROOTID'],
                            ]]);
                            if ($para['type']) {
                                sql_insert(["open_wechat_reply_contents", [
                                    "rid"       => $insert_id,
                                    "type"      => $para['type'],
                                    "order_no"  => "999999",
                                    "parameter" => arr2sql($para[$para['type']]),
                                ]]);
                            }
                            return $insert_id;
                        } else {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
                break;
        }
    }
    /**
     * [get_all_openid 获取所有已关注用户OPENID]
     * @param  array  $para [description]
     * @return [type]       [description]
     */
    public function get_all_openid($para = array())
    {
        $this->access_token();
        $url    = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->$cfg['access_token']['token']}";
        $url    = $para['next_openid'] ? $url . "&next_openid=" . $para['next_openid'] : $url;
        $result = json_decode(http::get($url), true);
        if ($result['total'] && ($result['total'] == $result['count'] || (($para['page'] - 1) * 10000 + $result['count']) == $result['total'])) {
            unset($result['next_openid']);
        }
        return $result;
    }
    /**
     * [get_material_count 获取永久素材总数]
     * @return [type] [description]
     */
    public function get_material_count()
    {
        $this->access_token();
        $result = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$this->$cfg['access_token']['token']}"), true);
        return $result;
    }
    public function get_material_list($para = array())
    {
        $this->access_token();
        $query = json_encode(array(
            "type"   => $para['type'] ? $para['type'] : "image",
            "offset" => $para['offset'] ? $para['offset'] : "0",
            "count"  => $para['count'] ? $para['count'] : "20",
        ));
        $result = json_decode(http::post("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$this->$cfg['access_token']['token']}", $query), true);
        return $result;
    }
    /**
     * [header_nocache 无缓存跳转]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public function header_nocache($url)
    {
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cahe, must-revalidate');
        header('Cache-Control: post-chedk=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url");
    }
    /**
     * [arr2xml 数组转xml]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public function arr2xml($arr)
    {
        if (!is_array($arr) || count($arr) == 0) {
            return false;
        } else {
            $xml = "<xml>";
            foreach ($arr as $key => $val) {
                if (is_numeric($val)) {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                }
            }
            $xml .= "</xml>";
            return $xml;
        }
    }
}
