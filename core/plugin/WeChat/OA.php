<?php
class OA
{
    public $cfg;
    public $session;
    public function __construct($config = [])
    {
        if (!$config) {
            $config    = LCMS::config(["name" => "wechat"]);
            $this->cfg = [
                "appid"     => $config['appid'],
                "appsecret" => $config['appsecret'],
                "thirdapi"  => $config['mode'] == "other" ? $config['access_api'] : "",
            ];
        } else {
            $this->cfg = [
                "appid"     => $config['appid'],
                "appsecret" => $config['appsecret'],
                "thirdapi"  => $config['thirdapi'],
            ];
        };
        $this->session = "LCMS" . strtoupper(substr(md5($this->cfg['appid']), 8, 16)) . "-";
        $this->cache();
    }
    /**
     * [cache 数据缓存读取与保存]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function cache($type = "get")
    {
        if ($this->cfg['appid'] && $this->cfg['appsecret']) {
            $cname = md5($this->cfg['appid'] . $this->cfg['appsecret']);
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cfg(["name" => $cname, "data" => $this->cfg]);
                break;
            default:
                $arr = LCMS::cfg(["name" => $cname]);
                if (is_array($arr)) {
                    $this->cfg = array_merge($arr, $this->cfg);
                }
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
        if (!$this->cfg['access_token']['token'] || $this->cfg['access_token']['expires'] < time()) {
            if ($this->cfg['thirdapi']) {
                // 如果启用第三方接口
                $token = json_decode(http::get($this->cfg['thirdapi'] . "accesstoken"), true);
                if ($token['code'] == "1" && $token['data']['access_token'] && $token['data']['expires_in']) {
                    $this->cfg['access_token'] = [
                        "token"   => $token['data']['access_token'],
                        "expires" => $token['data']['expires_in'],
                    ];
                    $this->cache("save");
                } else {
                    return $token;
                }
            } else {
                // 系统自处理
                $query = http_build_query([
                    "appid"      => $this->cfg['appid'],
                    "secret"     => $this->cfg['appsecret'],
                    "grant_type" => "client_credential",
                ]);
                $token = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/token?{$query}"), true);
                if ($token['errcode']) {
                    return $token;
                } else {
                    $this->cfg['access_token'] = [
                        "token"   => $token['access_token'],
                        "expires" => time() + 3600,
                    ];
                    $this->cache("save");
                }
            }
        }
        return $this->cfg['access_token']['token'];
    }
    /**
     * [getOpenidFromMp 使用code获取用户数据]
     * @param  [type] $code [code值]
     * @return [type]       [数组]
     */
    private function getOpenidFromMp($code)
    {
        $query = http_build_query([
            "appid"      => $this->cfg['appid'],
            "secret"     => $this->cfg['appsecret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        ]);
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
        $openid = SESSION::get($this->session . $scope);
        if ($openid['openid'] && $scope == "snsapi_base") {
            return $openid;
        } elseif ($openid['openid'] && $scope == "snsapi_userinfo" && $openid['expires_time'] > time()) {
            return $openid;
        } else {
            if ($_L['form']['wechatoauthopenid']) {
                // 设置数据Session
                $userinfo = $this->user([
                    "openid" => $_L['form']['wechatoauthopenid'],
                ]);
                if ($userinfo['openid']) {
                    SESSION::set($this->session . "snsapi_base", ["openid" => $userinfo['openid']]);
                    okinfo(url_clear($_L['url']['now'], "wechatoauthopenid"));
                }
            } else {
                if ($this->cfg['thirdapi']) {
                    // 如果启用第三方接口，跳转到第三方接口
                    $goback = urlencode($_L['url']['now']);
                    okinfo($this->cfg['thirdapi'] . "oauth&scope={$scope}&goback={$goback}&key=wechatoauthopenid");
                    exit();
                } else {
                    // 使用系统主前端域名进行授权
                    if ($_L['config']['web']['domain'] && $_L['config']['web']['domain'] != HTTP_HOST) {
                        $goback = urlencode($_L['url']['now']);
                        okinfo("{$_L['url']['sys']['own']}n=wechat&c=index&a=oauth&scope={$scope}&goback={$goback}");
                        exit();
                    }
                    // 用户授权登陆，获取code
                    $code = $_L['form']['code'];
                    if (!isset($code)) {
                        $query = http_build_query([
                            "appid"         => $this->cfg['appid'],
                            "redirect_uri"  => $_L['url']['now'],
                            "response_type" => "code",
                            "scope"         => $scope,
                        ]);
                        $this->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
                        exit();
                    } else {
                        // 使用code获取用户数据
                        $openid = $this->getOpenidFromMp($code);
                        if ($openid['openid']) {
                            $this->user([
                                "do"     => "save",
                                "openid" => $openid['openid'],
                                "wechat" => ["openid" => $openid['openid']],
                            ]);
                            $openid['expires_time'] = time() + 3600;
                            SESSION::set($this->session . $scope, $openid);
                            okinfo(url_clear($_L['url']['now'], "code|state"));
                        }
                    }
                }
            }
        }
    }
    /**
     * [userinfo 获取微信用户的详细信息]
     * @return [type] [数组]
     */
    public function userinfo($para = [])
    {
        global $_L;
        if ($para['type'] == "subscribe") {
            // 用户关注后获取用户信息
            $this->access_token();
            $query = http_build_query([
                "access_token" => $this->cfg['access_token']['token'],
                "openid"       => $para['openid'],
                "lang"         => "zh_CN",
            ]);
            $userinfo = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/user/info?{$query}"), true);
            if ($userinfo && !$userinfo['errcode']) {
                $userinfo = $this->user([
                    "do"     => "save",
                    "openid" => $userinfo['openid'],
                    "wechat" => $userinfo,
                ]);
            }
        } else {
            $userinfo = SESSION::get($this->session . "userinfo");
            if (!$userinfo['openid'] || $userinfo['errcode']) {
                if ($_L['form']['wechatoauthopenid']) {
                    // 跳转获取用户信息
                    if ($this->cfg['thirdapi']) {
                        // 如果启用第三方接口
                        $user = json_decode(http::get($this->cfg['thirdapi'] . "userinfo&openid={$_L['form']['wechatoauthopenid']}"), true);
                        if ($user['code'] == 1 && $user['data']['openid']) {
                            $userinfo = $this->user([
                                "do"     => "save",
                                "openid" => $user['data']['openid'],
                                "wechat" => $user['data'],
                            ]);
                        } else {
                            return $user;
                        }
                    } else {
                        $userinfo = $this->user([
                            "openid" => $_L['form']['wechatoauthopenid'],
                        ]);
                    }
                    if ($userinfo['openid']) {
                        SESSION::set($this->session . "userinfo", $userinfo);
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
                        $userinfo             = $this->user([
                            "do"     => "save",
                            "openid" => $userinfo['openid'],
                            "wechat" => $userinfo,
                        ]);
                        SESSION::set($this->session . "userinfo", $userinfo);
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
    public function user($para = [])
    {
        global $_L;
        $userinfo = sql_get(["open_wechat_user", "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
        if ($para['do'] == "save") {
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
            if ($userinfo && $form) {
                sql_update(["open_wechat_user", $form, "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
            } elseif ($form) {
                $form['openid'] = $para['wechat']['openid'];
                $form['lcms']   = $_L['ROOTID'];
                $userinfo['id'] = sql_insert(["open_wechat_user", $form]);
            }
            $userinfo = $userinfo && $form ? array_merge($userinfo, $form) : false;
        }
        if ($para['openid']) {
            $userinfo = $userinfo ? $userinfo : sql_get(["open_wechat_user", "openid = '{$para['openid']}' AND lcms = '{$_L['ROOTID']}'"]);
            if ($userinfo) {
                $userinfo['wechat'] = $userinfo;
            }
            return $userinfo;
        }
    }
    /**
     * [jsapi_ticket 获取全局 jsapi_ticket]
     * @return [type] [字符串]
     */
    public function jsapi_ticket()
    {
        $this->cache();
        if (!$this->cfg['jsapi_ticket']['ticket'] || $this->cfg['jsapi_ticket']['expires'] < time()) {
            if ($this->cfg['thirdapi']) {
                // 如果启用第三方接口
                $token = json_decode(http::get($this->cfg['thirdapi'] . "jsapiticket"), true);
                if ($token['code'] == "1" && $token['data']['ticket'] && $token['data']['expires_in']) {
                    $this->cfg['jsapi_ticket'] = [
                        "ticket"  => $token['data']['ticket'],
                        "expires" => $token['data']['expires_in'],
                    ];
                    $this->cache("save");
                } else {
                    return $token;
                }
            } else {
                $this->access_token();
                $query = http_build_query([
                    "access_token" => $this->cfg['access_token']['token'],
                    "type"         => "jsapi",
                ]);
                $ticket = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?{$query}"), true);
                if ($ticket['errcode']) {
                    return $ticket;
                } else {
                    $this->cfg['jsapi_ticket'] = [
                        "ticket"  => $ticket['ticket'],
                        "expires" => time() + 7000,
                    ];
                    $this->cache("save");
                }
            }
        }
        return $this->cfg['jsapi_ticket']['ticket'];
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
        $query       = implode("&", ["jsapi_ticket=" . $this->cfg['jsapi_ticket']['ticket'], "noncestr={$nonceStr}", "timestamp={$timestamp}", "url={$url}"]);
        $signPackage = [
            "appId"     => $this->cfg['appid'],
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => sha1($query),
            "rawString" => $query,
        ];
        return $signPackage;
    }
    /**
     * [send_tpl 发送模板消息]
     * @param  [type] $para [参数参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277 微信开发文档]
     * @return [type]       [description]
     */
    public function send_tpl($para = [])
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [send_custom 发送客服消息]
     * @param  [type] $para [参数参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140547 微信开发文档]
     * @return [type]       [description]
     */
    public function send_custom($para = [])
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->cfg['access_token']['token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [send_once 一次性订阅消息]
     * @param  [type] $para [开发参考 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1500374289_66bvB]
     * @return [type]       [description]
     */
    public function send_once($para = [])
    {
        global $_L;
        $this->access_token();
        $reserved = SESSION::get($this->session . "reserved");
        if (!$_L['form']['reserved']) {
            $reserved = randstr(32);
            SESSION::set($this->session . "reserved", $reserved);
            $query = http_build_query([
                "action"       => "get_confirm",
                "appid"        => $this->cfg['appid'],
                "scene"        => 1,
                "template_id"  => $para['template_id'],
                "redirect_url" => $para['redirect_url'],
                "reserved"     => $reserved,
            ]);
            okinfo("https://mp.weixin.qq.com/mp/subscribemsg?{$query}#wechat_redirect");
        } elseif ($_L['form']['reserved'] == $reserved) {
            $result = http::post("https://api.weixin.qq.com/cgi-bin/message/template/subscribe?access_token={$this->cfg['access_token']['token']}", json_encode_ex([
                "touser"      => $para['touser'],
                "template_id" => $para['template_id'],
                "miniprogram" => $para['miniprogram'],
                "scene"       => $_L['form']['scene'],
                "title"       => $para['title'],
                "data"        => $para['data'],
            ]));
            return $result;
        } else {
            return false;
        }
    }
    /**
     * [add_tpl 添加模板消息]
     * @param [type] $tpl [description]
     */
    public function add_tpl($tpl)
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->cfg['access_token']['token']}", json_encode_ex([
            "template_id_short" => $tpl,
        ]));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [del_tpl 删除模板消息]
     * @param  [type] $tplid [description]
     * @return [type]        [description]
     */
    public function del_tpl($tplid)
    {
        $this->access_token();
        $result = http::post("https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token={$this->cfg['access_token']['token']}", json_encode_ex([
            "template_id" => $tplid,
        ]));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [menu 设置、获取公众号菜单]
     * @param  [type] $para [description]
     * @return [type]       [数组，errcode为0代表成功]
     */
    public function menu($para = [])
    {
        $this->access_token();
        if (is_array($para)) {
            $result = http::post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->cfg['access_token']['token']}", json_encode_ex($para));
        } elseif ($para == "get") {
            $result = http::get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->cfg['access_token']['token']}");
        }
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * [material 素材上传]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function material($para = [])
    {
        $this->access_token();
        $file     = path_absolute($para['file']);
        $fileinfo = pathinfo($file);
        $size     = filesize($file);
        $mime     = [
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'mp3'  => 'audio/mp3',
            'wma'  => 'audio/x-ms-wma',
        ];
        if ($mime) {
            clearstatcache();
            $media = new \CURLFile($file, $mime[$fileinfo['extension']], $fileinfo['basename']);
            if ($para['temp']) {
                //临时素材
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->cfg['access_token']['token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            } else {
                //永久素材
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$this->cfg['access_token']['token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            }
            return json_decode($result, true);
        } else {
            return ["errcode" => 403, "errmsg" => "不支持的文件格式"];
        }
    }
    /**
     * [reply 关键词操作]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public function reply($para = [])
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
    public function get_all_openid($para = [])
    {
        $this->access_token();
        $url    = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->cfg['access_token']['token']}";
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
        $result = json_decode(http::get("https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$this->cfg['access_token']['token']}"), true);
        return $result;
    }
    public function get_material_list($para = [])
    {
        $this->access_token();
        $query = json_encode([
            "type"   => $para['type'] ? $para['type'] : "image",
            "offset" => $para['offset'] ? $para['offset'] : "0",
            "count"  => $para['count'] ? $para['count'] : "20",
        ]);
        $result = json_decode(http::post("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$this->cfg['access_token']['token']}", $query), true);
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
