<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-04-01 18:58:23
 * @Description:微信公众号接口类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class OA
{
    public $CFG, $SID;
    public function __construct($config = [])
    {
        global $_L, $LF, $SID;
        $LF = $_L['form'];
        if (!$config) {
            $config    = LCMS::config(["name" => "wechat"]);
            $this->CFG = [
                "appid"     => $config['appid'],
                "appsecret" => $config['appsecret'],
                "thirdapi"  => $config['mode'] == "other" ? $config['access_api'] : "",
            ];
        } else {
            $this->CFG = [
                "appid"     => $config['appid'],
                "appsecret" => $config['appsecret'],
                "thirdapi"  => $config['thirdapi'],
            ];
        };
        $SID = $this->SID = "WX" . strtoupper(substr(md5($this->CFG['appid'] . "RID{$_L['ROOTID']}"), 8, 16));
        $this->cache();
    }
    /**
     * @description: 数据缓存读取与保存
     * @param string $type
     * @return {*}
     */
    public function cache($type = "get")
    {
        global $_L, $LF, $SID;
        if ($this->CFG['appid'] && $this->CFG['appsecret']) {
            $cname = md5($this->CFG['appid'] . $this->CFG['appsecret']);
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
    /**
     * @description: 获取全局access_token
     * @param {*}
     * @return {*}
     */
    public function access_token()
    {
        global $_L, $LF, $SID;
        $this->cache();
        if (!$this->CFG['access_token']['token'] || $this->CFG['access_token']['expires'] < time()) {
            if ($this->CFG['thirdapi']) {
                // 如果启用第三方接口
                $token = json_decode(HTTP::get($this->CFG['thirdapi'] . "accesstoken"), true);
                if ($token['code'] == "1" && $token['data']['access_token'] && $token['data']['expires_in']) {
                    $this->CFG['access_token'] = [
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
                    "appid"      => $this->CFG['appid'],
                    "secret"     => $this->CFG['appsecret'],
                    "grant_type" => "client_credential",
                ]);
                $token = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/token?{$query}"), true);
                if (!$token['access_token']) {
                    return $token;
                } else {
                    $this->CFG['access_token'] = [
                        "token"   => $token['access_token'],
                        "expires" => time() + 3600,
                    ];
                    $this->cache("save");
                }
            }
        }
        return $this->CFG['access_token']['token'];
    }
    /**
     * @description: 使用code获取用户数据
     * @param string $code
     * @return array
     */
    public function getOpenidFromMp($code)
    {
        global $_L, $LF, $SID;
        $query = http_build_query([
            "appid"      => $this->CFG['appid'],
            "secret"     => $this->CFG['appsecret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        ]);
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/sns/oauth2/access_token?{$query}"), true);
        return $result ?: [];
    }
    /**
     * @description: 微信登陆获取openid
     * @param bool $type
     * @return array
     */
    public function openid($type = false)
    {
        global $_L, $LF, $SID;
        $this->cache();
        $scope  = $type ? "snsapi_userinfo" : "snsapi_base";
        $openid = SESSION::get($SID . $scope);
        if ($openid['openid'] && $scope == "snsapi_base") {
            return $openid;
        } elseif ($openid['openid'] && $scope == "snsapi_userinfo" && $openid['expires_time'] > time()) {
            return $openid;
        } else {
            $goback = urlencode($_L['url']['now']);
            if ($this->CFG['thirdapi']) {
                // 如果启用第三方接口，跳转到第三方接口
                okinfo($this->CFG['thirdapi'] . "oauth&scope={$scope}&rid={$_L['ROOTID']}&sid={$SID}&goback={$goback}");
            } else {
                //使用系统API域名进行授权
                if (stripos($_L['config']['web']['domain_api'], HTTP_HOST) === false) {
                    okinfo("{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=wechat&c=index&a=oauth&scope={$scope}&rid={$_L['ROOTID']}&sid={$SID}&goback={$goback}");
                }
                // 用户授权登陆，获取code
                if (!isset($LF['code'])) {
                    $query = http_build_query([
                        "appid"         => $this->CFG['appid'],
                        "redirect_uri"  => $_L['url']['now'],
                        "response_type" => "code",
                        "scope"         => $scope,
                    ]);
                    $this->header_nocache("https://open.weixin.qq.com/connect/oauth2/authorize?{$query}#wechat_redirect");
                } else {
                    // 使用code获取用户数据
                    $openid = $this->getOpenidFromMp($LF['code']);
                    if ($openid['openid']) {
                        $this->user([
                            "do"     => "save",
                            "openid" => $openid['openid'],
                            "data"   => [
                                "openid" => $openid['openid'],
                            ],
                        ]);
                        $openid['expires_time'] = time() + 3600;
                        SESSION::set($SID . $scope, $openid);
                        okinfo(url_clear($_L['url']['now'], "code|state"));
                    }
                }
            }
        }
    }
    /**
     * @description: 获取微信用户的详细信息
     * @param array $para
     * @return array
     */
    public function userinfo($para = [])
    {
        global $_L, $LF, $SID;
        switch ($para['type']) {
            case 'subscribe':
                $this->access_token();
                $token  = $this->CFG['access_token']['token'];
                $result = HTTP::get("https://api.weixin.qq.com/cgi-bin/user/info?" . http_build_query([
                    "access_token" => $token,
                    "openid"       => $para['openid'],
                    "lang"         => "zh_CN",
                ]));
                $userinfo = json_decode($result, true);
                if ($userinfo && !$userinfo['errcode']) {
                    $userinfo = $this->user([
                        "do"     => "save",
                        "openid" => $userinfo['openid'],
                        "data"   => $userinfo,
                    ]);
                }
                break;
            default:
                $userinfo = SESSION::get("{$SID}userinfo");
                if (!$userinfo['openid'] || $userinfo['errcode']) {
                    $openid = $this->openid(true);
                    $result = HTTP::get("https://api.weixin.qq.com/sns/userinfo?" . http_build_query([
                        "access_token" => $openid['access_token'],
                        "openid"       => $openid['openid'],
                        "lang"         => "zh_CN",
                    ]));
                    $userinfo = json_decode($result, true);
                    if ($userinfo && !$userinfo['errcode']) {
                        $userinfo = $this->user([
                            "do"     => "save",
                            "openid" => $userinfo['openid'],
                            "data"   => $userinfo,
                        ]);
                        SESSION::set("{$SID}userinfo", $userinfo);
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
        global $_L, $LF, $SID;
        $userinfo = sql_get(["open_wechat_user",
            "openid = :openid AND lcms = :lcms", "", [
                ":openid" => $para['openid'],
                ":lcms"   => $_L['ROOTID'],
            ]]);
        $userinfo = $userinfo ?: [];
        if ($para['do'] == "save") {
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
            return $userinfo ?: sql_get(["open_wechat_user",
                "openid = :openid AND lcms = :lcms", "", [
                    ":openid" => $para['openid'],
                    ":lcms"   => $_L['ROOTID'],
                ]]);
        }
    }
    /**
     * @description: 获取全局jsapi_ticket
     * @param {*}
     * @return {*}
     */
    public function jsapi_ticket()
    {
        global $_L, $LF, $SID;
        $this->cache();
        if (!$this->CFG['jsapi_ticket']['ticket'] || $this->CFG['jsapi_ticket']['expires'] < time()) {
            if ($this->CFG['thirdapi']) {
                // 如果启用第三方接口
                $token = json_decode(HTTP::get($this->CFG['thirdapi'] . "jsapiticket"), true);
                if ($token['code'] == "1" && $token['data']['ticket'] && $token['data']['expires_in']) {
                    $this->CFG['jsapi_ticket'] = [
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
                    "access_token" => $this->CFG['access_token']['token'],
                    "type"         => "jsapi",
                ]);
                $ticket = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?{$query}"), true);
                if (!$ticket['ticket']) {
                    return $ticket;
                } else {
                    $this->CFG['jsapi_ticket'] = [
                        "ticket"  => $ticket['ticket'],
                        "expires" => time() + 7000,
                    ];
                    $this->cache("save");
                }
            }
        }
        return $this->CFG['jsapi_ticket']['ticket'];
    }
    /**
     * @description: 获取前台JSSDK签名
     * @param string $url
     * @return array
     */
    public function signpackage($url = "")
    {
        global $_L, $LF, $SID;
        $this->jsapi_ticket();
        $url         = $url ?: $_L['url']['now'];
        $nonceStr    = randstr(16);
        $timestamp   = time();
        $query       = implode("&", ["jsapi_ticket=" . $this->CFG['jsapi_ticket']['ticket'], "noncestr={$nonceStr}", "timestamp={$timestamp}", "url={$url}"]);
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
        global $_L, $LF, $SID;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->CFG['access_token']['token']}", json_encode_ex($para));
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
        global $_L, $LF, $SID;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->CFG['access_token']['token']}", json_encode_ex($para));
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
        global $_L, $LF, $SID;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->CFG['access_token']['token']}", json_encode_ex([
            "template_id_short" => $tpl,
        ]));
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * @description: 删除模板消息
     * @param string $tplid
     * @return array
     */
    public function del_tpl($tplid)
    {
        global $_L, $LF, $SID;
        $this->access_token();
        $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token={$this->CFG['access_token']['token']}", json_encode_ex([
            "template_id" => $tplid,
        ]));
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
        global $_L, $LF, $SID;
        $this->access_token();
        if (is_array($para)) {
            $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->CFG['access_token']['token']}", json_encode_ex($para));
        } elseif ($para == "get") {
            $result = HTTP::get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->CFG['access_token']['token']}");
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
        global $_L, $LF, $SID;
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
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->CFG['access_token']['token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            } else {
                //永久素材
                $result = HTTP::post("https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$this->CFG['access_token']['token']}&type={$para['type']}", [
                    "media" => $media,
                ]);
            }
            return json_decode($result, true);
        } else {
            return ["errcode" => 403, "errmsg" => "不支持的文件格式"];
        }
    }
    /**
     * @description: 关键词操作
     * @param array $para
     * @return bool
     */
    public function reply($para = [])
    {
        global $_L, $LF, $SID;
        switch ($para['do']) {
            case 'del':
                if ($para['name']) {
                    $words = sql_get([
                        "open_wechat_reply_words",
                        "name = :name AND app = :app AND lcms = :lcms",
                        "", [
                            ":name" => $para['name'],
                            ":app"  => L_NAME,
                            ":lcms" => $_L['ROOTID'],
                        ],
                    ]);
                    foreach ([
                        "reply", "reply_words", "reply_contents",
                    ] as $table) {
                        $name = $table == "reply" ? "id" : "rid";
                        sql_delete([
                            "open_wechat_{$table}",
                            "{$name} = :rid",
                            [
                                ":rid" => $words['rid'],
                            ],
                        ]);
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
                    sql_delete([
                        "open_wechat_{$table}",
                        "app = :app AND lcms = :lcms",
                        [
                            ":app"  => L_NAME,
                            ":lcms" => $_L['ROOTID'],
                        ],
                    ]);
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
                        ],
                    ]);
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
                        $insert_id = sql_insert([
                            "open_wechat_reply",
                            [
                                "type"     => "2",
                                "app"      => L_NAME,
                                "order_no" => "999999",
                                "lcms"     => $_L['ROOTID'],
                            ],
                        ]);
                        if ($insert_id) {
                            sql_insert([
                                "open_wechat_reply_words",
                                [
                                    "rid"  => $insert_id,
                                    "name" => $para['name'],
                                    "app"  => L_NAME,
                                    "type" => "1",
                                    "lcms" => $_L['ROOTID'],
                                ],
                            ]);
                            if ($para['type']) {
                                sql_insert([
                                    "open_wechat_reply_contents",
                                    [
                                        "rid"       => $insert_id,
                                        "type"      => $para['type'],
                                        "order_no"  => "999999",
                                        "parameter" => arr2sql($para[$para['type']]),
                                    ],
                                ]);
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
     * @description: 获取所有已关注用户OPENID
     * @param array $para
     * @return array
     */
    public function get_all_openid($para = [])
    {
        global $_L, $LF, $SID;
        $this->access_token();
        $url    = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->CFG['access_token']['token']}";
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
        global $_L, $LF, $SID;
        $this->access_token();
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token={$this->CFG['access_token']['token']}"), true);
        return $result['kf_online_list'] ?: [];
    }
    /**
     * @description: 获取永久素材总数
     * @param {*}
     * @return {*}
     */
    public function get_material_count()
    {
        global $_L, $LF, $SID;
        $this->access_token();
        $result = json_decode(HTTP::get("https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$this->CFG['access_token']['token']}"), true);
        return $result ?: [];
    }
    /**
     * @description: 获取永久素材列表
     * @param array $para
     * @return array
     */
    public function get_material_list($para = [])
    {
        global $_L, $LF, $SID;
        $this->access_token();
        $query = json_encode([
            "type"   => $para['type'] ? $para['type'] : "image",
            "offset" => $para['offset'] ? $para['offset'] : "0",
            "count"  => $para['count'] ? $para['count'] : "20",
        ]);
        $result = json_decode(HTTP::post("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$this->CFG['access_token']['token']}", $query), true);
        return $result ?: [];
    }
    /**
     * @description: 无缓存跳转
     * @param string $url
     * @return {*}
     */
    public function header_nocache($url)
    {
        global $_L, $LF, $SID;
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cahe, must-revalidate');
        header('Cache-Control: post-chedk=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("HTTP/1.1 301 Moved Permanently");
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
        global $_L, $LF, $SID;
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
     * @description: 打开信息页面
     * @param array $page
     * @return {*}
     */
    public function page_msg($page = [])
    {
        global $_L, $LF, $SID;
        if ($this->CFG['thirdapi'] && stripos($this->CFG['thirdapi'], "/app/index.php?rootid=") !== false) {
            $url = $this->CFG['thirdapi'] . "msg&c=page&body=";
        } else {
            $url = "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=wechat&c=page&a=msg&body=";
        }
        okinfo($url . urlencode(base64_encode(json_encode([
            "icon"  => $page['icon'] ?: "success",
            "title" => $page['title'],
            "desc"  => $page['desc'],
            "info"  => $page['info'],
        ]))));
        exit;
    }
    /**
     * @description: 打开投诉页面
     * @param string $callback
     * @return {*}
     */
    public function page_tousu($callback = "")
    {
        global $_L, $LF, $SID;
        if ($this->CFG['thirdapi'] && stripos($this->CFG['thirdapi'], "/app/index.php?rootid=") !== false) {
            $url = $this->CFG['thirdapi'] . "tousu&c=page&callback=";
        } else {
            $url = "{$_L['config']['web']['domain_api']}app/index.php?rootid={$_L['ROOTID']}&n=wechat&c=page&a=tousu&callback=";
        }
        okinfo($url . urlencode($callback));
        exit;
    }
}
