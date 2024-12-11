<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-12-07 22:26:47
 * @Description: LCMS操作类
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LCMS
{
    /**
     * @description: 获取客户端真实IP
     * @param array $args
     * @return string
     */
    public static function IP($args = [])
    {
        $ip = "";
        if (!$args && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            //如果使用了CDN、负载均衡、代理
            $args = [
                "HTTP_ALI_CDN_REAL_IP", //阿里云
                "HTTP_EO_CONNECTING_IP", //腾讯TEO
                "HTTP_CF_CONNECTING_IP", //CloudFlare
                "HTTP_TRUE_CLIENT_IP", //百度云
                "HTTP_X_REAL_IP", //通用CDN
                "HTTP_X_FORWARDED_FOR", //通用CDN
            ];
        }
        foreach ($args as $arg) {
            if ($_SERVER[$arg]) {
                $ips    = explode(',', $_SERVER[$arg]);
                $ips[0] = trim($ips[0]);
                if (!is_intranet_ip($ips[0])) {
                    $ip = $ips[0];
                    break;
                }
            }
        }
        return $ip ?: $_SERVER["REMOTE_ADDR"];
    }
    /**
     * @description: 输出错误提示页面
     * @param int $code
     * @param string $msg
     * @param string $go
     * @return {*}
     */
    public static function X($code = 403, $msg = "拒绝访问", $go = "")
    {
        if ($_SERVER['CONTENT_TYPE'] === "application/json" || (strcasecmp($_SERVER["HTTP_X_REQUESTED_WITH"], "xmlhttprequest") === 0)) {
            ajaxout(0, $msg);
        } else {
            global $_L;
            $X = [
                "icon"  => "layui-icon-face-cry",
                "color" => "#F56C6C",
                "title" => "ERROR",
                "code"  => $code,
                "msg"   => $msg,
            ];
            require self::template(PATH_PUBLIC . "ui/admin/X");
        }
        exit;
    }
    /**
     * @description: 输出成功提示页面
     * @param int $code
     * @param string $msg
     * @param string $go
     * @return {*}
     */
    public static function Y($code = 200, $msg = "处理完成", $go = "")
    {
        if ($_SERVER['CONTENT_TYPE'] === "application/json" || (strcasecmp($_SERVER["HTTP_X_REQUESTED_WITH"], "xmlhttprequest") === 0)) {
            ajaxout(1, $msg);
        } else {
            global $_L;
            $X = [
                "icon"  => "layui-icon-face-smile",
                "color" => "#67C23A",
                "title" => "SUCCESS",
                "code"  => $code,
                "msg"   => $msg,
            ];
            require self::template(PATH_PUBLIC . "ui/admin/X");
        }
        exit;
    }
    /**
     * @description:判断是否为超级管理员
     * @param {*}
     * @return bool
     */
    public static function SUPER()
    {
        global $_L;
        return $_L['LCMSADMIN']['type'] === "lcms" ? true : false;
    }
    /**
     * @description: 硬盘缓存读写操作
     * @param string $name
     * @param string|array $value
     * @param bool $lcms
     * @return array
     */
    public static function cache($name, $value = [], $lcms = false)
    {
        global $_L;
        if (!in_string($name, "/")) {
            $name = L_NAME . "/{$name}";
        }
        $name  = substr(md5($name), 8, 16);
        $lcms  = $lcms ? 0 : $_L['ROOTID'];
        $cache = sql_get([
            "table" => "cache",
            "where" => "name = :name AND lcms = :lcms",
            "bind"  => [
                ":name" => $name,
                ":lcms" => $lcms,
            ],
        ]);
        if ($value === "clear") {
            sql_delete([
                "table" => "cache",
                "where" => "name = :name AND lcms = :lcms",
                "bind"  => [
                    ":name" => $name,
                    ":lcms" => $lcms,
                ],
            ]);
        } elseif ($value && is_array($value)) {
            if ($cache) {
                sql_update([
                    "table" => "cache",
                    "data"  => [
                        "parameter" => arr2sql($value),
                    ],
                    "where" => "name = :name AND lcms = :lcms",
                    "bind"  => [
                        ":name" => $name,
                        ":lcms" => $lcms,
                    ],
                ]);
            } else {
                sql_insert([
                    "table" => "cache",
                    "data"  => [
                        "name"      => $name,
                        "parameter" => arr2sql($value),
                        "lcms"      => $lcms,
                    ],
                ]);
            }
        } else {
            return $cache ? sql2arr($cache['parameter']) : [];
        }
    }
    /**
     * @description: 内存缓存读写操作
     * @param string $name
     * @param string $value
     * @param int $time
     * @param bool $lcms
     * @return string
     */
    public static function ram($name, $value = "", $time = 0, $lcms = false)
    {
        global $_L;
        if (!$_L['table']['ram']) {
            return "";
        }
        if (!in_string($name, "/")) {
            $name = L_NAME . "/{$name}";
        }
        $name = substr(md5(L_NAME . $name), 8, 16);
        $lcms = $lcms ? 0 : $_L['ROOTID'];
        $time = $time > 0 ? intval($time) : 86400;
        $ram  = sql_get([
            "table" => "ram",
            "where" => "name = :name AND lcms = :lcms",
            "bind"  => [
                ":name" => $name,
                ":lcms" => $lcms,
            ],
        ]);
        if ($value === "clear") {
            sql_delete([
                "table" => "ram",
                "where" => "name = :name AND lcms = :lcms",
                "bind"  => [
                    ":name" => $name,
                    ":lcms" => $lcms,
                ],
            ]);
        } elseif ($value) {
            if ($ram) {
                sql_update([
                    "table" => "ram",
                    "data"  => [
                        "value" => $value,
                        "time"  => time() + $time,
                    ],
                    "where" => "name = :name AND lcms = :lcms",
                    "bind"  => [
                        ":name" => $name,
                        ":lcms" => $lcms,
                    ],
                ]);
            } else {
                sql_insert([
                    "table" => "ram",
                    "data"  => [
                        "name"  => $name,
                        "value" => $value,
                        "time"  => time() + $time,
                        "lcms"  => $lcms,
                    ],
                ]);
                sql_delete([
                    "table" => "ram",
                    "where" => "time < :time",
                    "bind"  => [
                        ":time" => time(),
                    ],
                ]);
            }
        } else {
            return $ram && $ram['time'] > time() ? $ram['value'] : "";
        }
    }
    /**
     * @description: 添加系统通知
     * @param string $title
     * @param string $body
     * @param int $time
     * @param int|bool $lcms
     * @return {*}
     */
    public static function notify($title, $body = "", $time = 0, $lcms = false)
    {
        global $_L;
        if (!$_L['table']['notify']) {
            return false;
        }
        $code   = substr(md5(L_NAME . $title), 8, 16);
        $lcms   = $lcms ? 0 : $_L['ROOTID'];
        $notify = sql_get([
            "table" => "notify",
            "where" => "code = :code AND lcms = :lcms",
            "order" => "id DESC",
            "bind"  => [
                ":code" => $code,
                ":lcms" => $lcms,
            ],
        ]);
        if (!$notify || (time() - strtotime($notify['addtime'])) > $time) {
            sql_insert([
                "table" => "notify",
                "data"  => [
                    "code"    => $code,
                    "title"   => $title,
                    "content" => $body,
                    "addtime" => datenow(),
                    "lcms"    => $lcms,
                ],
            ]);
        }
        return true;
    }
    /**
     * @description: 全自动序列化配置保存操作
     * @param array $paran[do, form, name, type, cate, lcms, unset]
     * @return array
     */
    public static function config($paran = [])
    {
        global $_L;
        $para = [
            "do"    => $paran['do'] ?: "get",
            "form"  => $paran['form'] ?: $_L['form']['LC'],
            "name"  => $paran['name'] ?: L_NAME,
            "type"  => $paran['type'] ?: "open",
            "cate"  => $paran['cate'] ?: "auto",
            "lcms"  => $paran['lcms'] ? (is_numeric($paran['lcms']) ? $paran['lcms'] : 0) : $_L['ROOTID'],
            "unset" => $paran['unset'] ?: "",
        ];
        $config = sql_get([
            "table" => "config",
            "where" => "name = :name AND type = :type AND cate = :cate AND lcms = :lcms",
            "bind"  => [
                ":name" => $para['name'],
                ":type" => $para['type'],
                ":cate" => $para['cate'],
                ":lcms" => $para['lcms'] ?: 0,
            ],
        ]);
        if ($para['do'] === "save") {
            if ($config) {
                sql_update([
                    "table" => "config",
                    "data"  => [
                        "parameter" => arr2sql($config['parameter'], $para['form'], $para['unset']),
                    ],
                    "where" => "id = :id",
                    "bind"  => [
                        ":id" => $config['id'],
                    ],
                ]);
            } else {
                sql_insert([
                    "table" => "config",
                    "data"  => [
                        "name"      => $para['name'],
                        "type"      => $para['type'],
                        "cate"      => $para['cate'],
                        "parameter" => arr2sql($para['form']),
                        "lcms"      => $para['lcms'] ?: 0,
                    ],
                ]);
            }
        } else {
            return $config['parameter'] != "N;" ? sql2arr($config['parameter']) : [];
        };
    }
    /**
     * @description: 处理一般数据表的数据序列化保存与读取
     * @param array $paran[do, table, form, id, key, unset]
     * @return array
     */
    public static function form($paran = [])
    {
        global $_L;
        $form = $paran['form'] ?: ($_L['form']['LC'] ?: []);
        $para = [
            "do"    => $paran['do'] ?: "save",
            "table" => $paran['table'] ?: "",
            "id"    => $paran['id'] ?: (is_numeric($form['id']) ? $form['id'] : null),
            "key"   => $paran['key'] ?: "parameter",
            "unset" => $paran['unset'] ?: "",
        ];
        $data = $para['id'] ? sql_get([
            "table" => $para['table'],
            "where" => "id = :id",
            "bind"  => [
                ":id" => $para['id'],
            ]]) : [];
        if ($para['do'] === "get") {
            $data = array_merge($data, sql2arr($data[$para['key']]));
            return $data;
        }
        foreach ($form as $key => $val) {
            if (is_array($val)) {
                $parameter[$key] = $val;
                unset($form[$key]);
            }
        }
        if ($data) {
            if ($parameter) {
                if ($para['unset'] === true) {
                    //清空所有旧数据更新
                    $form[$para['key']] = arr2sql($parameter);
                } elseif ($para['unset']) {
                    //清空指定旧数据更新
                    $form[$para['key']] = arr2sql($data[$para['key']], $parameter, $para['unset']);
                } else {
                    //不清空旧数据直接覆盖
                    $form[$para['key']] = arr2sql($data[$para['key']], $parameter);
                }
            }
            sql_update([
                "table" => $para['table'],
                "data"  => $form,
                "where" => "id = :id",
                "bind"  => [
                    ":id" => $para['id'],
                ],
            ]);
        } else {
            if ($parameter) {
                $form[$para['key']] = arr2sql($parameter);
            }
            sql_insert([
                "table" => $para['table'],
                "data"  => $form,
            ]);
        }
    }
    /**
     * @description: 日志操作
     * @param array $paran[user, type, ip, info, url, postdata]
     * @return {*}
     */
    public static function log($paran = [])
    {
        global $_L;
        if (!$_L['table']['log']) {
            return "";
        }
        sql_delete([
            "table" => "log",
            "where" => "addtime < :addtime",
            "bind"  => [
                ":addtime" => datetime(time() - 31536000),
            ],
        ]);
        $para = [
            "user"      => $paran['user'] ?: ($_L['LCMSADMIN'] ? $_L['LCMSADMIN']['name'] : ""),
            "type"      => $paran['type'] ?: "",
            "ip"        => $paran['ip'] ?: CLIENT_IP . ":" . HTTP_PORT,
            "info"      => $paran['info'] ?: "",
            "url"       => $paran['url'] ?: "",
            "addtime"   => datenow(),
            "parameter" => $paran['postdata'] ? arr2sql([
                "postdata" => $paran['postdata'],
            ]) : "",
            "lcms"      => $paran['lcms'] ? (is_numeric($paran['lcms']) ? $paran['lcms'] : 0) : $_L['ROOTID'],
        ];
        $para['type'] && sql_insert([
            "table" => "log",
            "data"  => $para,
        ]);
    }
    /**
     * @description: 模板标签处理
     * @param string $tag
     * @return string
     */
    private static function tpltags($tag)
    {
        $tags = ["php", "template", "ui", "loop", "if", "elseif", "else", "switch", "case", "default"];
        foreach ($tags as $val) {
            if (strpos($tag, "<" . ($val === 'else' || $val === 'default' || $val === 'php' ? $val : "{$val} ")) !== false) {
                return $val;
                break;
            } elseif (strpos($tag, "</{$val}>") !== false) {
                return "/{$val}";
                break;
            }
        }
    }
    /**
     * @description: 模板缓存
     * @param string $path
     * @param string $ui
     * @return string
     */
    public static function template($path, $ui = "")
    {
        global $_L;
        $dir     = explode('/', $path);
        $postion = $dir[0];
        $fpath   = substr(stristr($path, '/'), 1);
        if ($postion === 'own') {
            $uipath = $ui ? "{$ui}/" : "";
            $file   = PATH_APP_OWN . "tpl/{$uipath}{$fpath}.html";
            $fpath  = str_replace(PATH_WEB, "", $file);
        } elseif ($postion === 'ui') {
            $file  = PATH_PUBLIC . "ui/" . L_MODULE . "/{$fpath}.html";
            $fpath = str_replace(PATH_WEB, "", $file);
        } else {
            $file  = "{$path}.html";
            $fpath = str_replace(PATH_WEB, "", $file);
        }
        if (!is_file($file)) {
            if ($_L['config']['admin']['development'] > 0) {
                LCMS::X(404, "模板文件未找到<br/>" . str_replace(PATH_WEB, "", $file));
            } else {
                LCMS::X(404, "模板文件未找到");
            }
        }
        $cname = substr(md5($fpath), 8, 16);
        $cache = PATH_CACHE . "tpl/{$cname}.php";
        if (filemtime($file) > filemtime($cache)) {
            $html = file_get_contents($file);
            //删除注释内容
            preg_match_all("/<!--(.*?)-->/is", $html, $notes);
            $html = str_replace($notes[0], "", $html);
            //批量替换固定字符串
            $html = str_replace([
                '<script type="text/javascript" onload>',
                '<script onload>',
            ], [
                '<script type="text/html" onload>',
                '<script type="text/html" onload>',
            ], $html);
            //标签替换
            preg_match_all("/{{(.*?)}}/i", $html, $match);
            preg_match_all("/<(.*?)(\/||'')>(?!=)/i", $html, $tags);
            foreach ($tags[0] as $index => $tag) {
                switch (self::tpltags($tag)) {
                    case 'php':
                        $html = str_replace($tag, "<?php ", $html);
                        break;
                    case 'template':
                        if (in_string($tag, [
                            'class="', 'id="', 'tplx="',
                        ])) {
                            $html = str_replace($tag, '<script type="text/html"' . str_replace("template", "", $tags[1][$index]) . ">", $html);
                        } else {
                            $html = str_replace($tag, "<?php require " . str_replace("template ", "LCMS::template(", $tags[1][$index]) . ", '" . ($uipath ? $ui : "") . "');?>", $html);
                        }
                        break;
                    case 'ui':
                        $html = str_replace($tag, "<?php " . str_replace(["ui table", "ui tree", "ui "], ["TABLE::html", "TABLE::tree", "LAY::"], $tags[1][$index]) . ";?>", $html);
                        break;
                    case 'loop':
                        $str = str_replace("loop ", "", $tags[1][$index]);
                        preg_match("/.*[\(|\[].*[\)|\]]/i", $str, $matchs);
                        if ($matchs && $matchs[0]) {
                            $str = str_replace($matchs[0], "", $str);
                        }
                        $str = explode(",", $str);
                        if ($matchs && $matchs[0]) {
                            $str[0] = $matchs[0];
                        }
                        foreach ($str as $i => $v) {
                            $str[$i] = trim($v);
                        }
                        $str  = array_filter($str);
                        $str  = array_values($str);
                        $str  = $str[2] ? "foreach ({$str[0]} as {$str[2]}=>{$str[1]})" : "foreach ({$str[0]} as {$str[1]})";
                        $html = str_replace($tag, '<?php ' . $str . '{ ?>', $html);
                        break;
                    case 'if':
                        $str  = str_replace(["if ", " gte ", " lte ", " gt ", " lt "], ["if (", ">=", "<=", ">", "<"], $tags[1][$index]);
                        $html = str_replace($tag, "<?php {$str}){ ?>", $html);
                        break;
                    case 'elseif':
                        $str  = str_replace(["elseif ", " gte ", " lte ", " gt ", " lt "], ["elseif (", " >= ", " <= ", " > ", " < "], $tags[1][$index]);
                        $html = str_replace($tag, "<?php } {$str}){ ?>", $html);
                        break;
                    case 'else':
                        $html = str_replace($tag, '<?php } else { ?>', $html);
                        break;
                    case 'switch':
                        $html = str_replace($tag, "<?php " . str_replace("switch ", "switch (", $tags[1][$index]) . "){ case 'LCMS" . time() . "': break;?>", $html);
                        break;
                    case 'case':
                    case 'default':
                        $html = str_replace($tag, "<?php {$tags[1][$index]}: ?>", $html);
                        break;
                    case '/case':
                    case '/default':
                        $html = str_replace($tag, "<?php break;?>", $html);
                        break;
                    case '/loop':
                    case '/if':
                    case '/switch':
                        $html = str_replace($tag, "<?php }?>", $html);
                        break;
                    case '/template':
                        $html = str_replace($tag, "</script>", $html);
                        break;
                    case '/ui':
                    case '/else':
                    case '/elseif':
                        $html = str_replace($tag, "", $html);
                        break;
                    case '/php':
                        $html = str_replace($tag, "?>", $html);
                        break;
                }
            }
            foreach ($match[0] as $key => $val) {
                $para = $match[1][$key];
                $nval = str_replace([
                    "{{  ", "{{ ", "  }}", " }}",
                ], [
                    "{{", "{{", "}}", "}}",
                ], $val);
                $para = rtrim(ltrim($para, " "), " ");
                if (stristr($nval, '{{$')) {
                    $rval = "<?php echo {$para}; ?>";
                } elseif (stristr($nval, '{{echo')) {
                    $rval = "<?php {$para}; ?>";
                } elseif (stristr($nval, '{{LCMS ')) {
                    $para = str_replace("LCMS ", "", $para);
                    $rval = "<?php {$para}; ?>";
                } elseif (stristr($nval, '{{#') === false) {
                    $para = str_replace("echo ", "", $para);
                    $rval = "<?php echo {$para}; ?>";
                }
                if ($rval) {
                    $html = str_replace($val, $rval, $html);
                }
            }
            $html = str_replace([
                '<block x-',
                '</block>',
            ], [
                '<template x-',
                '</template>',
            ], $html);
            $html = str_replace(["<%", "%>"], ["{{", "}}"], $html);
            $html = "<?php defined('IN_LCMS') or exit('No permission');?>" . PHP_EOL . $html;
            mkdir(PATH_CACHE . "tpl/");
            file_put_contents($cache, $html);
        }
        return $cache;
    }
}
