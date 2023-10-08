<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-10-01 12:20:08
 * @Description:微信公众号接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class OA
{
    public $CFG, $SID;
    public function __construct($config = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $LF = $_L['form'];
        if (!$config) {
            $config = LCMS::config([
                "name" => "wechat",
            ]);
            $config['thirdapi'] = $config['mode'] === "other" ? $config['access_api'] : "";
        };
        $this->CFG = [
            "oaname"    => $config['oaname'],
            "logo"      => oss($config['logo']),
            "appid"     => $config['appid'],
            "appsecret" => $config['appsecret'],
            "thirdapi"  => $config['thirdapi'],
        ];
        $this->SID = $SID = "WX" . strtoupper(substr(md5($this->CFG['appid'] . "RID{$_L['ROOTID']}"), 8, 16));
        $TRDAPI    = $config['thirdapi'];
        $this->cache();
    }
    /**
     * @description: 数据缓存读取与保存
     * @param string $type
     * @return {*}
     */
    public function cache($type = "get")
    {
        global $_L, $LF, $SID, $TRDAPI;
        if ($this->CFG['appid'] && $this->CFG['appsecret']) {
            $cname = $this->CFG['appid'] . $this->CFG['appsecret'];
        } else {
            return false;
        }
        switch ($type) {
            case 'save':
                LCMS::cache($cname, $this->CFG);
                break;
            case 'clear':
                LCMS::cache($cname, "clear");
                break;
            default:
                $arr = LCMS::cache($cname);
                if (is_array($arr)) {
                    $this->CFG = array_merge($arr, $this->CFG);
                }
                break;
        }
    }
    public function session($type = "get", $key, $value = "")
    {
        global $_L, $LF, $SID, $TRDAPI;
        switch ($type) {
            case 'get':
                $value = SESSION::get($SID . $key);
                $value = $value ?: [];
                return $value;
                break;
            case 'set':
                SESSION::set($SID . $key, $value);
                break;
        }
    }
    /**
     * @description: 获取全局access_token
     * @param {*}
     * @return {*}
     */
    public function access_token()
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->cache();
        if (!$this->CFG['access_token'] || $this->CFG['access_token']['expires_in'] < time()) {
            $token = HTTP::post("https://api.weixin.qq.com/cgi-bin/stable_token", json_encode([
                "grant_type" => "client_credential",
                "appid"      => $this->CFG['appid'],
                "secret"     => $this->CFG['appsecret'],
            ]));
            $token = json_decode($token, true);
            if ($token['access_token']) {
                $this->CFG['access_token'] = [
                    "access_token" => $token['access_token'],
                    "expires_in"   => $token['expires_in'] + time() - 300,
                ];
                $this->cache("save");
            } else {
                return $token;
            }
        }
        return $this->CFG['access_token'] ?: [];
    }
    /**
     * @description: 使用code获取用户数据
     * @param string $code
     * @return array
     */
    public function getOpenidFromMp($code)
    {
        global $_L, $LF, $SID, $TRDAPI;
        if (in_string($code, "OPENID|")) {
            $code = str_replace("OPENID|", "", $code);
            $code = json_decode(ssl_decode($code), true);
            if ($code['time'] > time()) {
                if ($TRDAPI) {
                    $result = HTTP::get("{$TRDAPI}userinfo&openid={$code['openid']}");
                    $result = json_decode($result, true);
                    if ($result['code'] == 1) {
                        $result = $result['data'];
                    }
                } else {
                    $result = $this->user([
                        "do"     => "get",
                        "openid" => $code['openid'],
                    ]);
                }
            } else {
                $result = [
                    "errcode" => 403,
                    "errmsg"  => "code error",
                ];
            }
        } else {
            $query = http_build_query([
                "appid"      => $this->CFG['appid'],
                "secret"     => $this->CFG['appsecret'],
                "code"       => $code,
                "grant_type" => "authorization_code",
            ]);
            $result = json_decode(HTTP::get("https://api.weixin.qq.com/sns/oauth2/access_token?{$query}"), true);
        }
        return $result ?: [];
    }
    /**
     * @description: 微信登陆获取openid
     * @param bool $type
     * @return array
     */
    public function openid($type = false)
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->cache();
        $scope  = $type ? "snsapi_userinfo" : "snsapi_base";
        $openid = $this->session("get", $scope);
        $goback = urlencode($_L['url']['now']);
        if ($openid['openid']) {
            //如果缓存存在
            switch ($scope) {
                case 'snsapi_userinfo':
                    return $openid;
                    break;
                case 'snsapi_base':
                    if ($openid['expires_in'] > time()) {
                        return $openid;
                    }
                    break;
            }
        }
        if ($LF['code'] && $LF['state'] == "LCMSWEIXINOAUTH") {
            //获取用户信息
            $openid = $this->getOpenidFromMp($LF['code']);
            if ($openid['openid']) {
                $this->user([
                    "do"     => "save",
                    "openid" => $openid['openid'],
                    "data"   => [
                        "openid" => $openid['openid'],
                    ],
                ]);
                $openid['expires_in'] = time() + 3600;
                $this->session("set", $scope, $openid);
                okinfo(url_clear($_L['url']['now'], "code|state"));
            }
            return $openid ?: [];
        } elseif ($TRDAPI) {
            //如果第三方接口
            okinfo("{$TRDAPI}oauth&scope={$scope}&goback={$goback}");
        } elseif (!in_string($_L['config']['web']['domain_api'], HTTP_HOST)) {
            //如果不是API域名
            okinfo("{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=wechat&c=index&a=oauth&scope={$scope}&goback={$goback}");
        } elseif ($scope == "snsapi_base" || $LF['LCMSWEIXINGOOAUTH'] == 1) {
            //发起授权
            $query = http_build_query([
                "appid"         => $this->CFG['appid'],
                "redirect_uri"  => $_L['url']['now'],
                "response_type" => "code",
                "scope"         => $scope,
                "state"         => "LCMSWEIXINOAUTH",
            ]);
            $this->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
        } else {
            //展示授权页
            exit(str_replace([
                "[oaname]", "[logo]", "[url]",
            ], [
                $this->CFG['oaname'],
                $this->CFG['logo'],
                "{$_L['url']['now']}&LCMSWEIXINGOOAUTH=1",
            ], file_get_contents(PATH_CORE . "plugin/WeChat/oauth.html")));
        }
    }
    /**
     * @description: 获取微信用户的详细信息
     * @param array $para
     * @return array
     */
    public function userinfo($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        switch ($para['type']) {
            case 'subscribe':
                $this->access_token();
                $result = HTTP::get("https://api.weixin.qq.com/cgi-bin/user/info?" . http_build_query([
                    "access_token" => $this->CFG['access_token']['access_token'],
                    "openid"       => $para['openid'],
                    "lang"         => "zh_CN",
                ]));
                $userinfo = json_decode($result, true);
                if ($userinfo && $userinfo['openid']) {
                    $userinfo = $this->user([
                        "do"     => "save",
                        "openid" => $userinfo['openid'],
                        "data"   => $userinfo,
                    ]);
                }
                break;
            default:
                $userinfo = $this->session("get", "userinfo");
                if (!$userinfo['openid']) {
                    $openid = $this->openid(true);
                    if ($openid['access_token']) {
                        $result = HTTP::get("https://api.weixin.qq.com/sns/userinfo?" . http_build_query([
                            "access_token" => $openid['access_token'],
                            "openid"       => $openid['openid'],
                            "lang"         => "zh_CN",
                        ]));
                        $userinfo = json_decode($result, true);
                    } else {
                        $userinfo = $openid;
                    }
                    if ($userinfo && $userinfo['openid']) {
                        $userinfo = $this->user([
                            "do"     => "save",
                            "openid" => $userinfo['openid'],
                            "data"   => $userinfo,
                        ]);
                        $this->session("set", "userinfo", $userinfo);
                    }
                }
                break;
        }
        return $userinfo ?: [];
    }
    /**
     * @description: 用户数据的保存与读取
     * @param array $para
     * @return {*}
     */
    public function user($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $userinfo = sql_get(["open_wechat_user",
            "openid = :openid AND lcms = :lcms", "", [
                ":openid" => $para['openid'],
                ":lcms"   => $_L['ROOTID'],
            ]]);
        $userinfo = $userinfo ?: [];
        if ($para['do'] === "save") {
            $form = [
                "openid"          => $para['data']['openid'],
                "subscribe"       => $para['data']['subscribe'],
                "nickname"        => $para['data']['nickname'],
                "language"        => $para['data']['language'],
                "headimgurl"      => $para['data']['headimgurl'],
                "subscribe_time"  => $para['data']['subscribe_time'],
                "unionid"         => $para['data']['unionid'],
                "remark"          => $para['data']['remark'],
                "groupid"         => $para['data']['groupid'],
                "subscribe_scene" => $para['data']['subscribe_scene'],
                "qr_scene"        => $para['data']['qr_scene'],
                "qr_scene_str"    => $para['data']['qr_scene_str'],
                "location"        => $para['data']['location'],
                "activetime"      => $para['data']['activetime'],
                "parameter"       => $para['data']['parameter'],
            ];
            foreach ($form as $key => $val) {
                if ($val === false || $val === "" || $val === null) {
                    unset($form[$key]);
                }
            }
            $form = $form ?: [];
            if ($userinfo && $form) {
                sql_update(["open_wechat_user",
                    $form, "openid = :openid AND lcms = :lcms", [
                        ":openid" => $para['openid'],
                        ":lcms"   => $_L['ROOTID'],
                    ]]);
            } elseif ($form['openid']) {
                $form['lcms']   = $_L['ROOTID'];
                $userinfo['id'] = sql_insert([
                    "open_wechat_user", $form,
                ]);
            }
            $userinfo = array_merge($userinfo, $form);
        }
        if ($para['openid']) {
            $userinfo = $userinfo ?: sql_get(["open_wechat_user",
                "openid = :openid AND lcms = :lcms", "", [
                    ":openid" => $para['openid'],
                    ":lcms"   => $_L['ROOTID'],
                ]]);
            unset($userinfo['id'], $userinfo['lcms']);
            return $userinfo ?: [];
        }
    }
    /**
     * @description: 获取全局jsapi_ticket
     * @param {*}
     * @return {*}
     */
    public function jsapi_ticket()
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->cache();
        if (!$this->CFG['jsapi_ticket'] || $this->CFG['jsapi_ticket']['expires_in'] < time()) {
            if ($TRDAPI) {
                //第三方接口
                $token = json_decode(HTTP::get("{$TRDAPI}jsapiticket"), true);
                if ($token['code'] == 1) {
                    $this->CFG['jsapi_ticket'] = $token['data'];
                    $this->cache("save");
                } else {
                    return $token;
                }
            } else {
                $this->access_token();
                $query = http_build_query([
                    "access_token" => $this->CFG['access_token']['access_token'],
                    "type"         => "jsapi",
                ]);
                $ticket = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?{$query}"), true);
                if ($ticket['ticket']) {
                    $this->CFG['jsapi_ticket'] = [
                        "ticket"     => $ticket['ticket'],
                        "expires_in" => time() + 7000,
                    ];
                    $this->cache("save");
                } else {
                    return $ticket;
                }
            }
        }
        return $this->CFG['jsapi_ticket'] ?: [];
    }
    /**
     * @description: 获取前台JSSDK签名
     * @param string $url
     * @return array
     */
    public function signpackage($url = "")
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->jsapi_ticket();
        $url       = $url ?: $_L['url']['now'];
        $nonceStr  = randstr(16);
        $timestamp = time();
        $query     = implode("&", [
            "jsapi_ticket=" . $this->CFG['jsapi_ticket']['ticket'],
            "noncestr={$nonceStr}",
            "timestamp={$timestamp}",
            "url={$url}",
        ]);
        $signPackage = [
            "appId"     => $this->CFG['appid'],
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => sha1($query),
            "rawString" => $query,
        ];
        return $signPackage;
    }
    /**
     * @description: 发送模板消息
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277
     * @param array $para
     * @return array
     */
    public function send_tpl($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 发送客服消息
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140547
     * @param array $para
     * @return array
     */
    public function send_custom($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 添加模板消息
     * @param string $tpl
     * @return array
     */
    public function add_tpl($tpl)
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex([
            "template_id_short" => $tpl,
        ]));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 删除模板消息
     * @param string $tplid
     * @return array
     */
    public function del_tpl($tplid)
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex([
            "template_id" => $tplid,
        ]));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 发送订阅消息
     * @param array $para
     * @return array
     */
    public function send_subscribe($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex($para));
        return json_decode($result, true);
    }
    /**
     * @description: 创建二维码
     * @param array $para
     * @return array
     */
    public function create_qrcode($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex($para));
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 设置、获取公众号菜单
     * @param array $para
     * @return array
     */
    public function menu($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        if (is_array($para)) {
            $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->CFG['access_token']['access_token']}", json_encode_ex($para));
        } elseif ($para === "get") {
            $result = HTTP::get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->CFG['access_token']['access_token']}");
        }
        $result = json_decode($result, true);
        return $result ?: [];
    }
    /**
     * @description: 素材上传
     * @param array $para
     * @return array
     */
    public function material($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
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
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->CFG['access_token']['access_token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            } else {
                //永久素材
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$this->CFG['access_token']['access_token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            }
            $result = json_decode($result, true);
        } else {
            $result = [
                "errcode" => 403,
                "errmsg"  => "不支持的文件格式",
            ];
        }
        return $result ?: [];
    }
    /**
     * @description: 关键词操作
     * @param array $para
     * @return bool
     */
    public function reply($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        switch ($para['do']) {
            case 'del':
                if ($para['name']) {
                    $words = sql_get(["open_wechat_reply_words",
                        "name = :name AND app = :app AND lcms = :lcms",
                        "", [
                            ":name" => $para['name'],
                            ":app"  => L_NAME,
                            ":lcms" => $_L['ROOTID'],
                        ]]);
                    foreach ([
                        "reply", "reply_words", "reply_contents",
                    ] as $table) {
                        $name = $table === "reply" ? "id" : "rid";
                        sql_delete(["open_wechat_{$table}",
                            "{$name} = :rid", [
                                ":rid" => $words['rid'],
                            ]]);
                    }
                    return true;
                } else {
                    return false;
                }
                break;
            case 'delall':
                foreach ([
                    "reply", "reply_words", "reply_contents",
                ] as $table) {
                    sql_delete(["open_wechat_{$table}",
                        "app = :app AND lcms = :lcms", [
                            ":app"  => L_NAME,
                            ":lcms" => $_L['ROOTID'],
                        ]]);
                }
                return true;
                break;
            default:
                if ($para['name'] && $para['class'] && $para['func']) {
                    $words = sql_get([
                        "open_wechat_reply_words",
                        "name = :name AND app = :app AND lcms = :lcms",
                        "", [
                            ":name" => $para['name'],
                            ":app"  => L_NAME,
                            ":lcms" => $_L['ROOTID'],
                        ]]);
                    if ($words) {
                        sql_update(["open_wechat_reply_contents", [
                            "parameter" => arr2sql([
                                "open" => [
                                    "class" => $para['class'],
                                    "func"  => $para['func'],
                                ],
                            ]),
                        ], "rid = :rid", [
                            ":rid" => $words['rid'],
                        ]]);
                        return $words['rid'];
                    } else {
                        $rid = sql_insert(["open_wechat_reply", [
                            "type"     => "2",
                            "app"      => L_NAME,
                            "order_no" => "999999",
                            "lcms"     => $_L['ROOTID'],
                        ]]);
                        if ($rid) {
                            sql_insert(["open_wechat_reply_words", [
                                "rid"  => $rid,
                                "name" => $para['name'],
                                "app"  => L_NAME,
                                "type" => "1",
                                "lcms" => $_L['ROOTID'],
                            ]]);
                            if ($para['type']) {
                                sql_insert(["open_wechat_reply_contents", [
                                    "rid"       => $rid,
                                    "type"      => $para['type'],
                                    "order_no"  => "999999",
                                    "parameter" => arr2sql($para[$para['type']]),
                                ]]);
                            }
                            return $rid;
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
     * @description: 获取所有已关注用户OPENID
     * @param array $para
     * @return array
     */
    public function get_all_openid($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $url    = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->CFG['access_token']['access_token']}";
        $url    = $para['next_openid'] ? $url . "&next_openid=" . $para['next_openid'] : $url;
        $result = json_decode(HTTP::get($url), true);
        if ($result['total'] && ($result['total'] == $result['count'] || (($para['page'] - 1) * 10000 + $result['count']) == $result['total'])) {
            unset($result['next_openid']);
        }
        return $result ?: [];
    }
    /**
     * @description: 获取当前在线客服列表
     * @param {*}
     * @return array|bool
     */
    public function get_custom_online()
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token={$this->CFG['access_token']['access_token']}"), true);
        return $result['kf_online_list'] ?: [];
    }
    /**
     * @description: 获取永久素材总数
     * @param {*}
     * @return {*}
     */
    public function get_material_count()
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$this->CFG['access_token']['access_token']}"), true);
        return $result ?: [];
    }
    /**
     * @description: 获取永久素材列表
     * @param array $para
     * @return array
     */
    public function get_material_list($para = [])
    {
        global $_L, $LF, $SID, $TRDAPI;
        $this->access_token();
        $query = json_encode([
            "type"   => $para['type'] ? $para['type'] : "image",
            "offset" => $para['offset'] ? $para['offset'] : "0",
            "count"  => $para['count'] ? $para['count'] : "20",
        ]);
        $result = json_decode(HTTP::post("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$this->CFG['access_token']['access_token']}", $query), true);
        return $result ?: [];
    }
    /**
     * @description: 无缓存跳转
     * @param string $url
     * @return {*}
     */
    public function header_nocache($url)
    {
        global $_L, $LF, $SID, $TRDAPI;
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
     * @description: 数组转xml
     * @param array $arr
     * @return string
     */
    public function arr2xml($arr)
    {
        global $_L, $LF, $SID, $TRDAPI;
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
    /**
     * @description: 打开投诉页面
     * @param string $callback
     * @return {*}
     */
    public function page_tousu($callback = "")
    {
        global $_L, $LF, $SID, $TRDAPI;
        if ($TRDAPI && in_string($TRDAPI, "/app/index.php?rootid=")) {
            $url = "{$TRDAPI}tousu&c=page&callback=";
        } else {
            $url = "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=wechat&c=page&a=tousu&callback=";
        }
        okinfo($url . urlencode($callback));
        exit;
    }
}
