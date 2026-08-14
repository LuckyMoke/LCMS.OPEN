<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2026-08-12 23:38:30
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
                "HTTP_EO_CLIENT_IP", //腾讯EO
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
     * @param array $args [code、title、msg、go]
     * @return {*}
     */
    public static function X(...$args)
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $args = [
            "code"  => $args['code'] ?: $args[0] ?: 403,
            "title" => $args['title'] ?? "ERROR",
            "msg"   => $args['msg'] ?: $args[1] ?: "拒绝访问",
            "go"    => $args['go'] ?: $args[2] ?: "",
        ];
        if (
            $_SERVER['HTTP_X_LCMS_KEY'] ||
            $_SERVER['HTTP_X_LCMS_TOKEN'] ||
            $_SERVER['HTTP_X_LCMS_FORMAT'] == "json" ||
            $_SERVER['CONTENT_TYPE'] == "application/json" ||
            $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" ||
            !is_file(PATH_PUBLIC . "ui/admin/X.html")
        ) {
            ajaxout(0, $args['msg']);
        } else {
            global $_L;
            $X = [
                "type"  => "error",
                "color" => "#F56C6C",
                "title" => $args['title'],
                "code"  => $args['code'],
                "msg"   => $args['msg'],
                "go"    => $args['go'],
            ];
            require self::template(PATH_PUBLIC . "ui/admin/X");
        }
        exit;
    }
    /**
     * @description: 输出成功提示页面
     * @param array $args [code、title、msg、go]
     * @return {*}
     */
    public static function Y(...$args)
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $args = [
            "code"  => $args['code'] ?: $args[0] ?: 200,
            "title" => $args['title'] ?? "SUCCESS",
            "msg"   => $args['msg'] ?: $args[1] ?: "处理完成",
            "go"    => $args['go'] ?: $args[2] ?: "",
        ];
        if (
            $_SERVER['HTTP_X_LCMS_KEY'] ||
            $_SERVER['HTTP_X_LCMS_TOKEN'] ||
            $_SERVER['HTTP_X_LCMS_FORMAT'] == "json" ||
            $_SERVER['CONTENT_TYPE'] == "application/json" ||
            $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" ||
            !is_file(PATH_PUBLIC . "ui/admin/X.html")
        ) {
            ajaxout(1, $args['msg']);
        } else {
            global $_L;
            $X = [
                "type"  => "success",
                "color" => "#67C23A",
                "title" => $args['title'],
                "code"  => $args['code'],
                "msg"   => $args['msg'],
                "go"    => $args['go'],
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
     * @param string $name 缓存名称
     * @param mixed $value 写入的数组
     * @param int|null $lcms 缓存层级ID
     * @return array|bool 读取时返回数组，写入或删除时返回布尔值
     */
    public static function cache($name, $value = [], $lcms = null)
    {
        global $_L;
        //静态内存缓存池
        static $_caches = [];
        //标准化缓存名称
        $path = PATH_CACHE . "system/";
        if (strpos($name, "/") === false) {
            $name = L_NAME . "/{$name}";
        }
        $name = substr(md5($name), 8, 16);
        $lcms = $lcms === null ? ($_L['ROOTID'] ?? 0) : (is_numeric($lcms) ? (int) $lcms : 0);
        $path = "{$path}{$lcms}/";
        $file = "{$path}{$name}.dat";
        $ckey = "{$lcms}:{$name}";
        //清除缓存
        if ($value === "clear") {
            if (is_file($file)) {
                delfile($file);
            }
            unset($_caches[$ckey]);
            return true;
        }
        //写入缓存
        if (is_array($value) && !empty($value)) {
            if (!is_dir($path)) {
                makedir($path);
            }
            $bytes = file_put_contents($file, json_encode_ex($value), LOCK_EX);
            if ($bytes === false) {
                LCMS::X(500, "数据写入失败，请检查程序目录权限");
            }
            $_caches[$ckey] = $value;
            return true;
        }
        //读取缓存
        if (isset($_caches[$ckey])) {
            return $_caches[$ckey];
        }
        if (is_file($file)) {
            $cache = file_get_contents($file);
            if ($cache) {
                $return = json_decode($cache, true);
            }
            $return         = $return ?? [];
            $_caches[$ckey] = $return;
            return $return;
        }
        //首次读取且文件不存在
        if (!is_dir($path)) {
            makedir($path);
        }
        return [];
    }
    /**
     * @description: 数据库内存缓存读写操作
     * @param string $name
     * @param mixed $value
     * @param int $time
     * @param int|null $lcms
     * @return string
     */
    public static function ram($name, $value = "", $time = 0, $lcms = null)
    {
        global $_L;
        if (!$_L['table']['ram']) {
            return "";
        }
        //静态内存缓存池
        static $_caches = [];
        //标准化缓存名称
        if (strpos($name, "/") === false) {
            $name = L_NAME . "/{$name}";
        }
        $name  = substr(md5(L_NAME . $name), 8, 16);
        $lcms  = $lcms === null ? ($_L['ROOTID'] ?? 0) : (is_numeric($lcms) ? (int) $lcms : 0);
        $time  = $time > 0 ? (int) $time : 86400;
        $ckey  = "{$lcms}:{$name}";
        $where = "name = :name AND lcms = :lcms";
        $bind  = [":name" => $name, ":lcms" => $lcms];
        //清除缓存
        if ($value === "clear") {
            sql_delete([
                "table" => "ram",
                "where" => $where,
                "bind"  => $bind,
            ]);
            unset($_caches[$ckey]);
            return "";
        }
        //写入/更新缓存
        if ($value !== "" && $value !== null) {
            $expire = time() + $time;
            $ram    = sql_get([
                "table" => "ram",
                "where" => $where,
                "bind"  => $bind,
            ]);
            if ($ram) {
                sql_update([
                    "table" => "ram",
                    "data"  => [
                        "value" => $value,
                        "time"  => $expire,
                    ],
                    "where" => $where,
                    "bind"  => $bind,
                ]);
            } else {
                sql_insert([
                    "table" => "ram",
                    "data"  => [
                        "name"  => $name,
                        "value" => $value,
                        "time"  => $expire,
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
            $_caches[$ckey] = [
                "value" => $value,
                "time"  => $expire,
            ];
            return "";
        }
        //读取缓存
        if (isset($_caches[$ckey])) {
            $ram = $_caches[$ckey];
            return $ram['time'] > time() ? $ram['value'] : "";
        }
        //内存没有，查询数据库
        $ram = sql_get([
            "table" => "ram",
            "where" => $where,
            "bind"  => $bind,
        ]);
        if ($ram && $ram['time'] > time()) {
            $_caches[$ckey] = $ram;
            return $ram['value'];
        }
        return "";
    }
    /**
     * @description: 添加系统通知
     * @param string $title
     * @param string $body
     * @param int $time
     * @param int|bool $lcms
     * @return {*}
     */
    public static function notify($title, $body = "", $time = 0, $lcms = null)
    {
        global $_L;
        if (!$_L['table']['notify']) {
            return false;
        }
        $code   = substr(md5(L_NAME . $title), 8, 16);
        $lcms   = isset($lcms) ? (is_numeric($lcms) ? $lcms : 0) : ($_L['ROOTID'] ?: 0);
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
     * @description: 系统配置读写操作
     * @param array $param[do,form,name,type,cate,lcms,unset]
     * @return array|bool
     */
    public static function config($param = [])
    {
        global $_L;
        //静态内存缓存池
        static $_caches = [];
        //规范化参数
        $para = [
            "do"    => $param['do'] ?? "get",
            "form"  => $param['form'] ?? $_L['form']['LC'],
            "name"  => $param['name'] ?? L_NAME,
            "type"  => $param['type'] ?? "open",
            "cate"  => $param['cate'] ?? "auto",
            "unset" => $param['unset'] ?? "",
            "lcms"  => $param['lcms'] ?? ($_L['ROOTID'] ?? 0),
        ];
        //确保lcms是数字类型
        $para['lcms'] = is_numeric($para['lcms']) ? (int) $para['lcms'] : 0;
        //通用变量
        $where = "name = :name AND type = :type AND cate = :cate AND lcms = :lcms";
        $bind  = [
            ":name" => $para['name'],
            ":type" => $para['type'],
            ":cate" => $para['cate'],
            ":lcms" => $para['lcms'],
        ];
        $ckey = md5("{$para['lcms']}:{$para['name']}{$para['type']}{$para['cate']}");
        //保存配置
        if ($para['do'] === "save") {
            $config = sql_get([
                "table" => "config",
                "where" => $where,
                "bind"  => $bind,
            ]);
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
                        "lcms"      => $para['lcms'],
                    ],
                ]);
            }
            unset($_caches[$ckey]);
            return true;
        }
        //读取配置逻辑
        if (isset($_caches[$ckey])) {
            return $_caches[$ckey];
        }
        //内存中不存在，从数据库中读取
        $config = sql_get([
            "table" => "config",
            "where" => $where,
            "bind"  => $bind,
        ]);
        $result = [];
        if (
            $config &&
            $config['parameter'] !== "N;" &&
            $config['parameter'] !== ""
        ) {
            $result = sql2arr($config['parameter']);
            $result = $result ?: [];
        }
        // 写入内存缓存
        $_caches[$ckey] = $result;
        return $result;
    }
    /**
     * @description: 处理一般数据表的数据序列化保存与读取
     * @param array $param[do,table,form,id,key,unset]
     * @return array|bool
     */
    public static function form($param = [])
    {
        global $_L;
        $form = $param['form'] ?? ($_L['form']['LC'] ?? []);
        $id   = $param['id'] ?? (isset($form['id']) && is_numeric($form['id']) ? $form['id'] : null);
        $lcms = $param['lcms'] ?? 0;
        $lcms = is_numeric($lcms) ? (int) $lcms : 0;
        $para = [
            "do"    => $param['do'] ?? "save",
            "table" => $param['table'] ?? "",
            "id"    => $id,
            "key"   => $param['key'] ?? "parameter",
            "unset" => $param['unset'] ?? "",
            "lcms"  => $lcms,
        ];
        $where = "id = :id";
        $bind  = [":id" => $para['id']];
        if ($para['lcms'] > 0) {
            $where .= " AND lcms = :lcms";
            $bind[":lcms"] = $para['lcms'];
        }
        //获取旧数据
        $data = [];
        if ($para['id']) {
            $data = sql_get([
                "table" => $para['table'],
                "where" => $where,
                "bind"  => $bind,
            ]) ?: [];
        }
        //读取数据
        if ($para['do'] === "get") {
            $_arr = [];
            if ($data[$para['key']]) {
                $_arr = sql2arr($data[$para['key']]);
                $_arr = $_arr ?: [];
            }
            return array_merge($data, $_arr);
        }
        //保存数据
        $_new = [];
        foreach ($form as $key => $val) {
            if (is_array($val)) {
                $_new[$key] = $val;
                unset($form[$key]);
            }
        }
        //统一处理序列化字段的生成
        if (!empty($_new)) {
            if ($data) {
                $_old = $data[$para['key']] ?? "";
                // 更新逻辑
                if ($para['unset'] === true) {
                    // 清空所有旧数据更新
                    $form[$para['key']] = arr2sql($_new);
                } elseif ($para['unset']) {
                    // 清空指定旧数据更新
                    $form[$para['key']] = arr2sql($_old, $_new, $para['unset']);
                } else {
                    // 不清空旧数据直接覆盖
                    $form[$para['key']] = arr2sql($_old, $_new);
                }
            } else {
                // 新增逻辑
                $form[$para['key']] = arr2sql($_new);
            }
        }
        //数据库写入
        if ($data) {
            sql_update([
                "table" => $para['table'],
                "data"  => $form,
                "where" => $where,
                "bind"  => $bind,
            ]);
        } else {
            sql_insert([
                "table" => $para['table'],
                "data"  => $form,
            ]);
        }
        return true;
    }
    /**
     * @description: 日志操作
     * @param array $paran[user, type, ip, info, url, postdata, lcms]
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
            "lcms"      => isset($paran['lcms']) ? (is_numeric($paran['lcms']) ? $paran['lcms'] : 0) : ($_L['ROOTID'] ?: 0),
        ];
        $para['type'] && sql_insert([
            "table" => "log",
            "data"  => $para,
        ]);
    }
    /**
     * @description: 模板缓存编译
     * @param string $path
     * @param string $ui
     * @return string
     */
    public static function template($path, $ui = "")
    {
        global $_L;
        $dir      = explode("/", $path);
        $postion  = $dir[0];
        $slashPos = strpos($path, "/");
        $fpath    = $slashPos !== false ? substr($path, $slashPos + 1) : "";
        $uipath   = "";
        switch ($postion) {
            case 'own':
                $uipath = $ui ? "{$ui}/" : "";
                $file   = PATH_APP_OWN . "tpl/{$uipath}{$fpath}.html";
                $fpath  = str_replace(PATH_WEB, "", $file);
                break;
            case 'ui':
                $file  = PATH_PUBLIC . "ui/" . L_MODULE . "/{$fpath}.html";
                $fpath = str_replace(PATH_WEB, "", $file);
                break;
            case 'app':
                $file  = PATH_APP . "/{$fpath}.html";
                $fpath = str_replace(PATH_WEB, "", $file);
                break;
            default:
                $file  = "{$path}.html";
                $fpath = str_replace(PATH_WEB, "", $file);
                break;
        }
        if (!is_file($file)) {
            $msg = $_L['config']['admin']['development'] > 0 ? "模板文件未找到<br>" . str_replace(PATH_WEB, "", $file) : "模板文件未找到";
            LCMS::X(404, $msg);
        }
        $cname = substr(md5($fpath), 8, 16);
        $cache = PATH_CACHE . "tpl/{$cname}.php";
        $ctime = 0;
        if (is_file($cache)) {
            $ctime = filemtime($cache);
        }
        if (filemtime($file) > $ctime) {
            $html = file_get_contents($file);
            //删除注释内容
            $html = preg_replace("/<!--.*?-->/is", "", $html);
            //批量替换固定字符串
            $html = str_replace([
                '<script type="text/javascript" onload>',
                '<script onload>',
            ], [
                '<script type="text/html" onload>',
                '<script type="text/html" onload>',
            ], $html);
            //标签替换
            preg_match_all("/<(\/?)(php|template|ui|loop|if|elseif|else|switch|case|default)(\s[^>]*?)?\/?>/is", $html, $tags);
            foreach ($tags[0] as $index => $tag) {
                $slash = $tags[1][$index];
                $name  = $tags[2][$index];
                $attrs = trim($tags[3][$index]);
                $tname = "{$slash}{$name}";
                $tbody = trim("{$name} {$attrs}");
                switch ($tname) {
                    case 'php':
                        $html = str_replace($tag, "<?php ", $html);
                        break;
                    case 'template':
                        if (in_string($attrs, [
                            'class="', 'id="', 'tplx="',
                        ])) {
                            $html = str_replace($tag, "<script type=\"text/html\" {$attrs}>", $html);
                        } else {
                            $restr = $uipath ? $ui : "";
                            if (
                                !empty($attrs) &&
                                preg_match('/^[a-zA-Z0-9_\/]+$/', $attrs)
                            ) {
                                $attrs = "\"{$attrs}\"";
                            }
                            $html = str_replace($tag, "<?php require LCMS::template({$attrs}, \"{$restr}\");?>", $html);
                        }
                        break;
                    case 'ui':
                        $restr = str_replace([
                            "ui table", "ui tree", "ui ",
                        ], [
                            "TABLE::html", "TABLE::tree", "LAY::",
                        ], $tbody);
                        $html = str_replace($tag, "<?php {$restr};?>", $html);
                        break;
                    case 'loop':
                        $str = $attrs;
                        preg_match("/.*[\(|\[].*[\)|\]]/i", $str, $matchs);
                        if (!empty($matchs[0])) {
                            $str = str_replace($matchs[0], "", $str);
                        }
                        $parts = explode(",", $str);
                        if (!empty($matchs[0])) {
                            $parts[0] = $matchs[0];
                        }
                        $parts = array_filter(array_map('trim', $parts));
                        $parts = array_values($parts);
                        $restr = !empty($parts[2]) ? "foreach ({$parts[0]} as {$parts[2]}=>{$parts[1]})" : "foreach ({$parts[0]} as {$parts[1]})";
                        $html  = str_replace($tag, "<?php {$restr}{ ?>", $html);
                        break;
                    case 'if':
                        $str = str_replace([
                            " gte ", " lte ", " gt ", " lt ",
                        ], [
                            ">=", "<=", ">", "<",
                        ], $attrs);
                        $html = str_replace($tag, "<?php if ({$str}){ ?>", $html);
                        break;
                    case 'elseif':
                        $str = str_replace([
                            " gte ", " lte ", " gt ", " lt ",
                        ], [
                            " >= ", " <= ", " > ", " < ",
                        ], $attrs);
                        $html = str_replace($tag, "<?php } elseif ({$str}){ ?>", $html);
                        break;
                    case 'else':
                        $html = str_replace($tag, "<?php } else { ?>", $html);
                        break;
                    case 'switch':
                        $html = str_replace($tag, "<?php switch ({$attrs}){ case 'LCMS" . time() . "': break;?>", $html);
                        break;
                    case 'case':
                    case 'default':
                        $html = str_replace($tag, "<?php {$tbody}: ?>", $html);
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
            preg_match_all("/{{(.*?)}}/i", $html, $match);
            foreach ($match[0] as $key => $val) {
                $para = trim($match[1][$key]);
                $rval = null;
                $nval = str_replace([
                    "{{  ", "{{ ", "  }}", " }}",
                ], [
                    "{{", "{{", "}}", "}}",
                ], $val);
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
                if ($rval !== null) {
                    $html = str_replace($val, $rval, $html);
                }
            }
            $html = str_replace([
                "<block x-", "</block>",
            ], [
                "<template x-", "</template>",
            ], $html);
            $html  = "<?php defined('IN_LCMS') or exit('No permission');?>" . PHP_EOL . $html;
            $cpath = PATH_CACHE . "tpl/";
            if (!is_dir($cpath)) {
                mkdir($cpath, 0755, true);
            }
            file_put_contents($cache, $html, LOCK_EX);
        }
        return $cache;
    }
}
