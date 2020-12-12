<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-11 15:30:32
 * @Description:LCMS操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LCMS
{
    /**
     * @获取客户端真实IP
     * @param {*}
     * @return {*}
     */
    public static function IP()
    {
        $iplib = ["HTTP_ALI_CDN_REAL_IP", "HTTP_TRUE_CLIENT_IP", "HTTP_X_REAL_FORWARDED_FOR", "HTTP_X_CONNECTING_IP", "HTTP_CF_CONNECTING_IP", "HTTP_X_REAL_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR"];
        foreach ($iplib as $val) {
            if (isset($_SERVER[$val]) && $_SERVER[$val] && strcasecmp($_SERVER[$val], "unknown")) {
                $ips = explode(',', $_SERVER[$val]);
                $ip  = $ips[0];
                break;
            }
        }
        return $ip;
    }
    /**
     * @输出错误提示页面
     * @param {*}
     * @return {*}
     */
    public static function X($errcode, $errmsg, $go = "")
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            ajaxout(0, $errmsg ?: "拒绝访问！");
        } else {
            global $_L;
            $X["code"] = $errcode ?: 403;
            $X["msg"]  = $errmsg ?: "拒绝访问！";
            require self::template(PATH_PUBLIC . "ui/admin/X");
        }
        exit;
    }
    /**
     * @判断是否为超级管理员
     * @param {*}
     * @return {*}
     */
    public static function SUPER()
    {
        global $_L;
        if ($_L['LCMSADMIN']['type'] == "lcms") {
            return true;
        }
    }
    /**
     * @cache保存读取
     * @param {*}
     * @return {*}
     */
    public static function cache($para = [])
    {
        global $_L;
        $file = PATH_CACHE . "cfg/" . md5(L_NAME . $para['name'] . ($para['lcms'] == true ? "0" : $_L['ROOTID'])) . ".cache";
        if (!$para['data'] && is_file($file)) {
            return sql2arr(file_get_contents($file));
        } elseif ($para['data']) {
            makedir(PATH_CACHE . "cfg/");
            file_put_contents($file, arr2sql($para['data']));
        }
    }
    /**
     * @全自动序列化配置保存操作
     * @param {*}
     * @return {*}
     */
    public static function config($paran = [])
    {
        global $_L;
        $form = $_L['form']['LC'];
        $para = array(
            "do"    => $paran['do'] ? $paran['do'] : "get",
            "name"  => $paran['name'] ? $paran['name'] : L_NAME,
            "type"  => $paran['type'] ? $paran['type'] : "open",
            "cate"  => $paran['cate'] ? $paran['cate'] : "auto",
            "lcms"  => $paran['lcms'] ? (is_numeric($paran['lcms']) ? $paran['lcms'] : "0") : $_L['ROOTID'],
            "unset" => $paran['unset'] ? $paran['unset'] : "",
        );
        $config = sql_get(["config", "name = '{$para['name']}' AND type = '{$para['type']}' AND cate = '{$para['cate']}' AND lcms = '{$para['lcms']}'"]);
        if ($para['do'] == "save") {
            if ($config) {
                sql_update(["config", [
                    "parameter" => arr2sql($config['parameter'], $form, $para['unset']),
                ], "id = '{$config['id']}'"]);
            } else {
                sql_insert(["config", [
                    "name"      => $para['name'],
                    "type"      => $para['type'],
                    "cate"      => $para['cate'],
                    "parameter" => arr2sql($form),
                    "lcms"      => $para['lcms'],
                ]]);
            }
        } else {
            $config = sql2arr($config['parameter']);
            return $config != "N;" ? $config : "";
        };
    }
    /**
     * [form 处理非config数据表的数据序列化保存与读取]
     * @param  [type] $table  [description]
     * @param  string $getid  [description]
     * @param  string $arrkey [description]
     * @return [type]         [description]
     */
    public static function form($paran = [])
    {
        global $_L;
        $form = $_L['form']['LC'];
        $para = array(
            "do"    => $paran['do'] ? $paran['do'] : "save",
            "table" => $paran['table'] ? $paran['table'] : "",
            "id"    => $paran['id'] ? $paran['id'] : ($form['id'] ? $form['id'] : ""),
            "key"   => $paran['key'] ? $paran['key'] : "parameter",
            "unset" => $paran['unset'] ? $paran['unset'] : false,
        );
        $data = $para['id'] ? sql_get([$para['table'], "id = '{$para['id']}'"]) : [];
        if ($para['do'] == "get") {
            $parameter = sql2arr($data[$para['key']]);
            foreach ($parameter as $key => $val) {
                $data[$key] = $val;
            }
            return $data;
        }
        foreach ((array)$form as $key => $val) {
            if (is_array($val)) {
                $parameter[$key] = $val;
                unset($form[$key]);
            }
        }
        if ($data) {
            if ($parameter) {
                if ($para['unset'] === true) {
                    $form[$para['key']] = empty($parameter) ? "" : arr2sql($parameter);
                } elseif ($para['unset']) {
                    $form[$para['key']] = empty($parameter) ? "" : arr2sql($data[$para['key']], $parameter, $para['unset']);
                } else {
                    $form[$para['key']] = empty($parameter) ? "" : arr2sql($data[$para['key']], $parameter);
                }
            }
            sql_update([$para['table'], $form, "id = '{$para['id']}'"]);
        } else {
            $parameter ? $form[$para['key']] = arr2sql($parameter) : "";
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
        $dir      = explode('/', $path);
        $postion  = $dir[0];
        $filename = substr(stristr($path, '/'), 1);
        if ($postion == 'own') {
            $uipath   = $ui ? "{$ui}/" : "";
            $file     = PATH_APP_OWN . "tpl/{$uipath}{$filename}.html";
            $filename = str_replace(PATH_WEB, "", $file);
        } elseif ($postion == 'ui') {
            $file     = PATH_PUBLIC . "ui/" . L_MODULE . "/{$filename}.html";
            $filename = str_replace(PATH_WEB, "", $file);
        } else {
            $file     = "{$path}.html";
            $filename = str_replace(PATH_WEB, "", $file);
        }
        $cache = PATH_CACHE . "tpl/" . md5($filename) . ".php";
        if (!is_file($file) && !is_file($cache)) {
            LCMS::X(404, "{$filename} 文件未找到");
        }
        if (is_file($file) && @filemtime($file) > @filemtime($cache)) {
            // if (1) {
            $html = file_get_contents($file);
            preg_match_all("/{{(.*?)}}/i", $html, $match);
            preg_match_all("/<(.*?)(\/||'')>(?!=)/i", $html, $tags);
            // 新版模板标签处理
            foreach ($tags[0] as $index => $tag) {
                switch (self::tpltags($tag)) {
                    case 'php':
                        $html = str_replace($tag, "<?php ", $html);
                        break;
                    case 'template':
                        $html = str_replace($tag, "<?php require " . str_replace("template ", "LCMS::template(", $tags[1][$index]) . ", '" . ($uipath ? $ui : "") . "');?>", $html);
                        break;
                    case 'ui':
                        $html = str_replace($tag, "<?php " . str_replace(["ui table", "ui tree", "ui "], ["table::html", "table::tree", "LAY::"], $tags[1][$index]) . ";?>", $html);
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
            // 旧版模板标签处理，淘汰中
            foreach ($match[0] as $key => $val) {
                $newval         = str_replace(array("{{  ", "{{ ", "  }}", " }}"), array("{{", "{{", "}}", "}}"), $val);
                $match[1][$key] = ltrim($match[1][$key], " ");
                $match[1][$key] = rtrim($match[1][$key], " ");
                if (stristr($newval, '{{$')) {
                    $html = str_replace($val, '<?php echo ' . $match[1][$key] . '; ?>', $html);
                } elseif (stristr($newval, '{{if')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . '{ ?>', $html);
                } elseif (stristr($newval, '{{else')) {
                    $html = str_replace($val, '<?php }' . $match[1][$key] . '{ ?>', $html);
                } elseif (stristr($newval, '{{foreach')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . '{ ?>', $html);
                } elseif (stristr($newval, '{{loop')) {
                    $str  = str_replace("loop ", "", $match[1][$key]);
                    $str  = explode(",", $str);
                    $str  = $str[2] ? "foreach ({$str[0]} as {$str[2]}=>{$str[1]})" : "foreach ({$str[0]} as {$str[1]})";
                    $html = str_replace($val, '<?php ' . $str . '{ ?>', $html);
                } elseif (stristr($newval, '{{switch')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . '{ default: ?>', $html);
                } elseif (stristr($newval, '{{case')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . ': ?>', $html);
                } elseif (stristr($newval, '{{/case') || stristr($newval, '{{/break')) {
                    $html = str_replace($val, '<?php break;?>', $html);
                } elseif (stristr($newval, '{{php}}')) {
                    $html = str_replace($val, '<?' . $match[1][$key], $html);
                } elseif (stristr($newval, '{{/php}}')) {
                    $html = str_replace($val, ' ?>', $html);
                } elseif (stristr($newval, 'LCMS::template')) {
                    $html = str_replace($val, '<?php require ' . $match[1][$key] . ';?>', $html);
                } elseif (stristr($newval, '{{/')) {
                    $html = str_replace($val, '<?php }?>', $html);
                } elseif (stristr($newval, '{{echo')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . ' ?>', $html);
                } elseif (stristr($newval, '{{LCMS ')) {
                    $match[1][$key] = str_replace("LCMS ", "", $match[1][$key]);
                    $html           = str_replace($val, '<?php ' . $match[1][$key] . ' ?>', $html);
                } elseif (stristr($newval, '{{LAY::')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . ';?>', $html);
                } elseif (stristr($newval, '{{table::')) {
                    $html = str_replace($val, '<?php ' . $match[1][$key] . ';?>', $html);
                } elseif (stristr($newval, '{{#') === false) {
                    $match[1][$key] = str_replace("echo ", "", $match[1][$key]);
                    $html           = str_replace($val, "<?php echo {$match[1][$key]} ?>", $html);
                }
            }
            if (!file_exists(PATH_CACHE . "tpl/")) {
                @clearstatcache();
                $fileUrl = '';
                foreach (explode('/', PATH_CACHE . "tpl/") as $val) {
                    $fileUrl .= $val . '/';
                    if (!file_exists($fileUrl)) {
                        mkdir($fileUrl);
                    }
                }
                @clearstatcache();
            }
            file_put_contents($cache, "<?php defined('IN_LCMS') or exit('No permission');?>" . PHP_EOL . $html);

        }
        return $cache;
    }
}
