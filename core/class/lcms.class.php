<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-12-13 15:27:20
 * @Description: LCMS操作类
 * @Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LCMS
{
    /**
     * @description: 获取客户端真实IP
     * @param {*}
     * @return string
     */
    public static function IP()
    {
        $headers = ["HTTP_ALI_CDN_REAL_IP", "HTTP_TRUE_CLIENT_IP", "HTTP_X_REAL_FORWARDED_FOR", "HTTP_X_CONNECTING_IP", "HTTP_CF_CONNECTING_IP", "HTTP_X_FORWARD_FOR", "HTTP_X_REAL_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR"];
        foreach ($headers as $header) {
            if ($_SERVER[$header]) {
                $ips = explode(',', $_SERVER[$header]);
                if ($ips[0] && filter_var($ips[0], FILTER_VALIDATE_IP)) {
                    $ip = $ips[0];
                    break;
                }
            }
        }
        return $ip ?: "";
    }
    /**
     * @description: 输出错误提示页面
     * @param int $code
     * @param string $msg
     * @param string $go
     * @return {*}
     */
    public static function X($code = 403, $msg = "拒绝访问！", $go = "")
    {
        if ($_SERVER['CONTENT_TYPE'] == "application/json" || (strcasecmp($_SERVER["HTTP_X_REQUESTED_WITH"], "xmlhttprequest") === 0)) {
            ajaxout(0, $msg);
        } else {
            global $_L;
            $X = [
                "code" => $code,
                "msg"  => $msg,
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
        return $_L['LCMSADMIN']['type'] == "lcms" ? true : false;
    }
    /**
     * @description:
     * @param {string} $name
     * @param {string|array} $para
     * @param {bool} $lcms
     * @return {*}
     */
    /**
     * @description: 系统缓存读写操作
     * @param string $name
     * @param string|array $para
     * @param bool $lcms
     * @return array
     */
    public static function cache($name, $para = [], $lcms = false)
    {
        global $_L;
        $name  = substr(md5(L_NAME . $name), 8, 16);
        $lcms  = $lcms ? 0 : $_L['ROOTID'];
        $cache = sql_get(["cache",
            "name = :name AND lcms = :lcms", "", [
                ":name" => $name,
                ":lcms" => $lcms,
            ]]);
        if (!$para && $cache) {
            return sql2arr($cache['parameter']);
        } elseif ($para == "clear") {
            sql_delete(["cache",
                "name = :name AND lcms = :lcms", [
                    ":name" => $name,
                    ":lcms" => $lcms,
                ]]);
        } elseif (is_array($para)) {
            if ($cache) {
                sql_update(["cache", [
                    "parameter"  => arr2sql($para),
                    "updatetime" => datenow(),
                ], "name = :name AND lcms = :lcms", [
                    ":name" => $name,
                    ":lcms" => $lcms,
                ]]);
            } else {
                sql_insert(["cache", [
                    "name"       => $name,
                    "parameter"  => arr2sql($para),
                    "updatetime" => datenow(),
                    "lcms"       => $lcms,
                ]]);
            }
        }
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
        $config = sql_get(["config",
            "name = :name AND type = :type AND cate = :cate AND lcms = :lcms",
            "", [
                ":name" => $para['name'],
                ":type" => $para['type'],
                ":cate" => $para['cate'],
                ":lcms" => $para['lcms'],
            ]]);
        if ($para['do'] == "save") {
            if ($config) {
                sql_update(["config", [
                    "parameter" => arr2sql($config['parameter'], $para['form'], $para['unset']),
                ], "id = :id", [
                    ":id" => $config['id'],
                ]]);
            } else {
                sql_insert(["config", [
                    "name"      => $para['name'],
                    "type"      => $para['type'],
                    "cate"      => $para['cate'],
                    "parameter" => arr2sql($para['form']),
                    "lcms"      => $para['lcms'],
                ]]);
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
        $data = $para['id'] ? sql_get([$para['table'],
            "id = :id", "", [
                ":id" => $para['id'],
            ]]) : [];
        if ($para['do'] == "get") {
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
            sql_update([$para['table'],
                $form, "id = :id", [
                    ":id" => $para['id'],
                ]]);
        } else {
            if ($parameter) {
                $form[$para['key']] = arr2sql($parameter);
            }
            sql_insert([$para['table'], $form]);
        }
    }
    /**
     * @模板标签处理
     * @param {*}
     * @return {*}
     */
    private static function tpltags($tag)
    {
        $tags = ["php", "template", "ui", "loop", "if", "elseif", "else", "switch", "case", "default"];
        foreach ($tags as $val) {
            if (strpos($tag, "<" . ($val == 'else' || $val == 'default' || $val == 'php' ? $val : "{$val} ")) !== false) {
                return $val;
                break;
            } elseif (strpos($tag, "</{$val}>") !== false) {
                return "/{$val}";
                break;
            }
        }
    }
    /**
     * @模板处理
     * @param {*}
     * @return {*}
     */
    public static function template($path, $ui = "")
    {
        global $_L;
        $dir     = explode('/', $path);
        $postion = $dir[0];
        $fpath   = substr(stristr($path, '/'), 1);
        if ($postion == 'own') {
            $uipath = $ui ? "{$ui}/" : "";
            $file   = PATH_APP_OWN . "tpl/{$uipath}{$fpath}.html";
            $fpath  = str_replace(PATH_WEB, "", $file);
        } elseif ($postion == 'ui') {
            $file  = PATH_PUBLIC . "ui/" . L_MODULE . "/{$fpath}.html";
            $fpath = str_replace(PATH_WEB, "", $file);
        } else {
            $file  = "{$path}.html";
            $fpath = str_replace(PATH_WEB, "", $file);
        }
        is_file($file) || LCMS::X(404, "{$fpath} 文件未找到");
        $cname = substr(md5($fpath), 8, 16);
        $cache = PATH_CACHE . "tpl/{$cname}.php";
        if (filemtime($file) > filemtime($cache)) {
            $html = file_get_contents($file);
            preg_match_all("/{{(.*?)}}/i", $html, $match);
            preg_match_all("/<(.*?)(\/||'')>(?!=)/i", $html, $tags);
            foreach ($tags[0] as $index => $tag) {
                switch (self::tpltags($tag)) {
                    case 'php':
                        $html = str_replace($tag, "<?php ", $html);
                        break;
                    case 'template':
                        $html = str_replace($tag, "<?php require " . str_replace("template ", "LCMS::template(", $tags[1][$index]) . ", '" . ($uipath ? $ui : "") . "');?>", $html);
                        break;
                    case 'ui':
                        $html = str_replace($tag, "<?php " . str_replace(["ui table", "ui tree", "ui "], ["TABLE::html", "TABLE::tree", "LAY::"], $tags[1][$index]) . ";?>", $html);
                        break;
                    case 'loop':
                        $str  = explode(",", str_replace("loop ", "", $tags[1][$index]));
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
            mkdir(PATH_CACHE . "tpl/");
            $html = str_replace(["<%", "%>"], ["{{", "}}"], $html);
            $html = "<?php defined('IN_LCMS') or exit('No permission');?>" . PHP_EOL . $html;
            file_put_contents($cache, $html);
        }
        return $cache;
    }
}
