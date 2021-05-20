<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2021-05-18 23:25:45
 * @Description: 全局方法
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * @description: 输出字符串或数组
 * @param mixed $vars
 * @return string
 */
function dump($vars)
{
    $content = "<pre>\n";
    $content .= htmlspecialchars(print_r($vars, true), ENT_COMPAT, 'ISO-8859-1');
    $content .= "</pre>\n";
    echo $content;
}
/**
 * @description: 数组转换为json，不转义中文
 * @param array $arr
 * @return string|null
 */
function json_encode_ex($arr = "")
{
    return json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
/**
 * @description: AJAX输出的标准数据
 * @param int $code 返回状态 1,0
 * @param string $msg 返回提示
 * @param string $go 跳转链接,可选
 * @param mixed $data 输出的数据,可选
 * @return string
 */
function ajaxout($code = 1, $msg = "", $go = "", $data = "")
{
    $arr = array(
        "code" => $code,
        "msg"  => $msg,
        "go"   => $go,
        "data" => $data,
    );
    echo json_encode_ex($arr);
    exit;
}
/**
 * @description: 数组序列化
 * @param array $old
 * @param array $new
 * @param string $unarr
 * @return string
 */
function arr2sql($old = [], $new = [], $unarr = "")
{
    $old = sql2arr($old);
    if ($old && is_array($new) && !empty($new)) {
        // 新老数据合并
        $unarr = explode("|", $unarr);
        foreach ($unarr as $unstr) {
            if ($unstr) {
                $unstr = explode("/", $unstr);
                $unnum = count($unstr);
                switch ($unnum) {
                    case 1:
                        unset($old[$unstr[0]]);
                        break;
                    case 2:
                        unset($old[$unstr[0]][$unstr[1]]);
                        break;
                    case 3:
                        unset($old[$unstr[0]][$unstr[1]][$unstr[2]]);
                        break;
                    case 4:
                        unset($old[$unstr[0]][$unstr[1]][$unstr[2]][$unstr[3]]);
                        break;
                }
            }
        }
        $sql = serialize($new ? array_replace_recursive((array) $old, $new) : $old);
    } elseif ($old) {
        // 使用老数据
        $sql = serialize($old);
    } else {
        // 使用新数据
        $sql = serialize($new);
    }
    return is_serialize($sql) ? $sql : "";
}
/**
 * @description: 反序列化
 * @param mixed $data
 * @return array|bool
 */
function sql2arr($data = "")
{
    if (empty($data)) {
        return false;
    }
    if (is_array($data)) {
        return $data;
    }
    if (!is_serialize($data)) {
        return false;
    }
    $result = unserialize($data, [
        'allowed_classes' => false,
    ]);
    if (false === $result) {
        $cache = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
            return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
        }, $data);
        return unserialize($cache);
    } else {
        return $result;
    }
}
/**
 * @description: 获取当前时间
 * @param {*}
 * @return string
 */
function datenow()
{
    return date("Y-m-d H:i:s");
}
/**
 * @description: 时间的转换、截取
 * @param string|int $date
 * @param string $type
 * @return string
 */
function datetime($date, $type = "")
{
    $date     = is_numeric($date) ? date("Y-m-d H:i:s", $date) : $date;
    $datetime = explode(" ", $date);
    $_date    = explode("-", $datetime[0]);
    $_time    = explode(":", $datetime[1]);
    switch ($type) {
        case 'date':
            return $datetime[0];
            break;
        case 'time':
            return $datetime[1];
            break;
        case 'y':
            return $_date[0];
            break;
        case 'm':
            return $_date[1];
            break;
        case 'd':
            return $_date[2];
            break;
        case 'h':
            return $_time[0];
            break;
        case 'i':
            return $_time[1];
            break;
        case 's':
            return $_time[2];
            break;
        default:
            return $date;
            break;
    }
}
/**
 * @description: 正常时间转unix时间戳
 * @param string $date
 * @return int
 */
function unixtime($date)
{
    return strtotime($date);
}
/**
 * @description: 获取毫秒时间戳
 * @param {*}
 * @return int
 */
function microseconds()
{
    $microseconds = microtime();
    $microseconds = explode(" ", $microseconds);
    $microseconds = floatval($microseconds[0]) * 1000000;
    $microseconds = str_pad($microseconds, 6, "0", STR_PAD_LEFT);
    return $microseconds;
}
/**
 * @description: 获取随机字符串
 * @param int $length 获取长度
 * @param string $type 获取类型:all,num,let
 * @return string
 */
function randstr($length = 4, $type = "all")
{
    switch ($type) {
        case 'num':
        case 'number':
            $str = "0123456789";
            break;
        case 'let':
        case 'letter':
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZ";
            break;
        default:
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
            break;
    }
    $result = "";
    for ($i = 0; $i < $length; $i++) {
        $num[$i] = mt_rand(0, strlen($str) - 1);
        $result .= $str[$num[$i]];
    }
    return $result;
}
/**
 * @description: 获取两数之间的随机数，含小数
 * @param int $min
 * @param int $max
 * @return int
 */
function randfloat($min = 0, $max = 1)
{
    return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
}
/**
 * @description: 清理URL中的参数
 * @param string $url
 * @param string $keys
 * @return string
 */
function url_clear($url, $keys)
{
    $urls  = parse_url($url);
    $keys  = explode("|", $keys);
    $query = explode("&", $urls['query']);
    foreach ($query as $index => $val) {
        $str = explode("=", $val);
        foreach ($keys as $key) {
            if ($str[0] == $key) {
                unset($query[$index]);
                continue;
            }
        }
    }
    $qry = implode("&", $query);
    $url = $urls['scheme'] ? "{$urls['scheme']}://" : "";
    $url .= $urls['host'] ? $urls['host'] : "";
    $url .= $urls['port'] ? ":{$urls['port']}" : "";
    $url .= $urls['path'] ? $urls['path'] : "";
    $url .= $qry ? "?{$qry}" : "";
    $url .= $urls['fragment'] ? "#{$urls['fragment']}" : "";
    return $url;
}
/**
 * @description: URL去除scheme
 * @param string $url
 * @return string
 */
function url_auto($url)
{
    $url = str_replace(["http://", "https://"], "//", $url);
    return $url;
}
/**
 * @description: JS页面跳转
 * @param string $url
 * @param string $info
 * @return {*}
 */
function okinfo($url, $info = "")
{
    $url = url_clear($url, "lcmstips");
    $url = $url ?: "";
    if ($info) {
        $info = urlencode($info);
        if (stristr($url, "?")) {
            $url = $url . "&lcmstips=" . $info;
        } else {
            $url = $url . "?lcmstips=" . $info;
        }
    }
    echo '<!DOCTYPE html><html><head><title></title><meta name="renderer" content="webkit"/><meta charset="utf-8"/><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/><meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/><link rel="icon" href="data:image/ico;base64,aWNv"/></head><body><style>html,body{margin:0;width:100%;height:100%;background:#FFFFFF;}</style><div style="width:100px;height:100px;position:absolute;margin:-50px 0 0 -50px;top:50%;left:50%;text-align:center;"><img src="data:image/gif;base64,R0lGODlhPAA8ALMIAGZmZoCAgODg4JmZmczMzDMzMwAAAP///////wAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkM4MUU2MTEwQkJEODExRThCRURDQzlEOTAxOEQ4NERBIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkM4MUU2MTExQkJEODExRThCRURDQzlEOTAxOEQ4NERBIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QzgxRTYxMEVCQkQ4MTFFOEJFRENDOUQ5MDE4RDg0REEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QzgxRTYxMEZCQkQ4MTFFOEJFRENDOUQ5MDE4RDg0REEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4B//79/Pv6+fj39vX08/Lx8O/u7ezr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"><p style="position:relative;margin-top:-12px;font-size:14px;">加载中...</p></div>
<script type="text/javascript">window.onload=function(){window.location.href="' . $url . '"};</script></body></html>';
    exit;
}
/**
 * @description: 302跳转
 * @param string $url
 * @return {*}
 */
function goheader($url)
{
    header("Location: $url");
    exit;
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function gbk2utf8($str = "")
{
    return mb_convert_encoding($str, "UTF-8", "GBK, GB2312, BIG5, ASCII");
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function utf82gbk($str = "")
{
    return mb_convert_encoding($str, "GBK", "UTF-8");
}
/**
 * @description: 字符串过滤
 * @param array|string $string
 * @return array|string
 */
function sqlinsert($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = sqlinsert($val);
        }
    } else {
        $string = str_ireplace([
            "\0", "\\", "*", "%5C", "%22", "%27", "select", "insert", "update", "delete", "union", "into", "load_file", "outfile", "sleep",
        ], [
            "_", "_", "\*", "&#92;", "&#34;", "&#39;", "sel\ect", "ins\ert", "up\date", "del\ete", "un\ion", "in\to", "load\_file", "out\file", "sl\eep",
        ], $string);
        $string = trim($string);
    }
    return $string;
}
/**
 * @description: 全局表单内容过滤
 * @param array|string $string
 * @return array|string
 */
function filterform($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = filterform($val);
        }
    } else {
        if (L_MODULE != "admin") {

            $string = trim(sqlinsert($string));
        } else {
            $string = trim($string);
        }
    }
    return $string;
}
/**
 * @description: 过滤Emoji
 * @param string $str
 * @return string
 */
function filterEmoji($str)
{
    $str = preg_replace_callback('/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}
/**
 * @description: 人民币小写转大写
 * @param string $rmb
 * @return string
 */
function rmb($rmb)
{
    $cnums           = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖");
    $cnyunits        = array("圆", "角", "分");
    $grees           = array("拾", "佰", "仟", "万", "拾", "佰", "仟", "亿");
    list($ns1, $ns2) = explode(".", $rmb, 2);
    $ns2             = array_filter(array($ns2[1], $ns2[0]));
    $ret             = array_merge($ns2, array(implode("", _cny_map_unit(str_split($ns1), $grees)), ""));
    $ret             = implode("", array_reverse(_cny_map_unit($ret, $cnyunits)));
    return str_replace(array_keys($cnums), $cnums, $ret);
}
function _cny_map_unit($list, $units)
{
    $ul = count($units);
    $xs = array();
    foreach (array_reverse($list) as $x) {
        $l = count($xs);
        if ($x != "0" || !($l % 4)) {
            $n = ($x == '0' ? '' : $x) . ($units[($l - 1) % $ul]);
        } else {
            $n = is_numeric($xs[0][0]) ? $x : '';
        }
        array_unshift($xs, $n);
    }
    return $xs;
}
/**
 * @description: 字符串截取
 * @param string $str
 * @param int $start
 * @param int $length
 * @return string|bool
 */
function strcut($str, $start = 0, $length = 0)
{
    $code = mb_detect_encoding($str, "ASCII,UTF-8,GB2312,GBK", true);
    $len  = mb_strlen($str, $code);
    if ($start > $len) {
        return false;
    }
    if (-$start > $len) {
        return $str;
    }
    if (empty($length) && $length !== 0) {
        if ($start < 0) {
            $length = -$start;
            $start  = $len + $start;
        } else {
            $length = $len - $start;
        }
    }
    if ($length < 0) {
        return false;
    }
    $newstr = mb_substr($str, $start, $length, $code);
    return $newstr;
}
/**
 * @description: 字符串模糊
 * @param string $str
 * @param int $start
 * @param int $end
 * @return string
 */
function strstar($str, $start, $end = 0)
{
    $len = mb_strlen($str, "UTF-8");
    if ($start == 0 && $start >= $len) {
        $start = $len - 1;
    }
    if ($end != 0 && $end >= $len) {
        $end = $len - $start - 1;
        $end = $end < 0 ?: 0;
    }
    if (($start + $end) >= $len) {
        $end = 0;
    }
    if (($start + $end) >= $len) {
        $start--;
    }
    $endStart = $len - $end;
    $top      = mb_substr($str, 0, $start, "UTF-8");
    $bottom   = "";
    if ($endStart > 0) {
        $bottom = mb_substr($str, $endStart, $end, "UTF-8");
    }
    $len    = $len - mb_strlen($top, "UTF-8");
    $len    = $len - mb_strlen($bottom, "UTF-8");
    $newStr = $top;
    for ($i = 0; $i < $len; $i++) {
        $newStr .= "*";
    }
    $newStr .= $bottom;
    return $newStr;
}
/**
 * @description: AES字符串加密
 * @param string $string
 * @param string $key
 * @return string
 */
function ssl_encode($string, $key = "LCMS")
{

    $encrypt = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    $encrypt = strtolower(bin2hex($encrypt));
    return $encrypt;
}
/**
 * @description: AES字符串解密
 * @param string $string
 * @param string $key
 * @return string
 */
function ssl_decode($string, $key = "LCMS")
{
    $decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return $decrypted;
}
/**
 * @description: 替换富文本中图片懒加载
 * @param string $str
 * @return string
 */
function lazyload($str = "")
{
    $str = preg_replace('/(<img[^>]*)src(=["\'][^>]*>)/', '$1 class="lazyload" data-src$2', $str);
    $str = preg_replace('/(<iframe[^>]*)src(=["\'][^>]*>)/', '$1 class="lazyload" data-src$2', $str);
    return $str;
}
/**
 * @description: 检测是否手机端
 * @param string $ua
 * @return bool
 */
function is_mobile($ua = "")
{
    $ismobile = 0;
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        $ismobile++;
    }
    if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT']) {
        $ismobile++;
    }
    if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) {
        $ismobile++;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        if (preg_match("/(nokia|sony|ericsson|mot|samsung|htc|sgh|lg|sharp|sie-|philips|panasonic|alcatel|lenovo|iphone|ipod|blackberry|meizu|android|netfront|symbian|ucweb|windowsce|palm|operamini|operamobi|openwave|nexusone|cldc|midp|wap|mobile" . ($ua ? '|' . $ua : '') . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $ismobile++;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            $ismobile++;
        }
    }
    return $ismobile > 0 ? true : false;
}
/**
 * @description: 判断是否为base64编码
 * @param string $str
 * @return bool
 */
function is_base64($str = "")
{
    return $str == base64_encode(base64_decode($str)) ? true : false;
}
/**
 * @description: 判断是否为手机号
 * @param string $phone
 * @return bool
 */
function is_phone($phone)
{
    if (strlen($phone) == 11) {
        if ($phone && preg_match("/^1[3-9]\d{9}$/", $phone)) {
            $flag = true;
        } else {
            $flag = false;
        }
    } else {
        $flag = false;
    }
    return $flag;
}
/**
 * @description: 判断是否为序列化
 * @param mixed $data
 * @return boolean
 */
function is_serialize($data)
{
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return false;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $semicolon = strpos($data, ';');
    $brace     = strpos($data, '}');
    if (false === $semicolon && false === $brace) {
        return false;
    }
    if (false !== $semicolon && $semicolon < 3) {
        return false;
    }
    if (false !== $brace && $brace < 4) {
        return false;
    }
    return true;
}
/**
 * @description: 判断一个链接是否为https
 * @param string $url
 * @return bool
 */
function is_https($url = "")
{
    if (stripos($url, "https://") !== false) {
        return true;
    }
}
/**
 * @description: 判断一个字符串是否为url
 * @param string $str
 * @return bool
 */
function is_url($str = "")
{
    if (is_string($str) && strpos($str, "://") !== false) {
        return true;
    }
}
/**
 * @description: HTML内容解码懒加载
 * @param string $content
 * @param bool $lazyload
 * @return string
 */
function html_editor($content = "", $lazyload = true)
{
    if ($content) {
        $content = is_base64($content) ? base64_decode($content) : $content;
        preg_match_all("/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/i", $content, $match);
        if ($match[1]) {
            $imgs = [];
            foreach ($match[1] as $index => $img) {
                $imgs[] = str_replace($img, oss($img), $match[0][$index]);

            }
            $content = str_replace($match[0], $imgs, $content);
        }
        if ($lazyload) {
            $content = preg_replace('/(<img[^>]*)src(=["\'][^>]*>)/', '$1class="lazyload" data-src$2', $content);
            $content = preg_replace('/(<video[^>]*)src="(.*?)"(.*?)<\/video>/', '<video class="video-js" data-src="$2"$3></video>', $content);
            $content = preg_replace('/(<iframe[^>]*)src="(.*?)"(.*?)><\/iframe>/', '<iframe frameborder="0" data-src="$2"$3></iframe>', $content);
        }
        $content = str_replace(["<p><br /></p>", "<p><br/></p>", "<p><br></p>"], "", $content);
    }
    return $content ?: "";
}
/**
 * @description: 获取云存储链接
 * @param string $url
 * @return string
 */
function oss($url)
{
    global $_L;
    if (!is_url($url) && $_L['plugin']['oss']['type'] != "local") {
        $preg = "../upload/";
        if (strpos($url, $preg) !== false) {
            $url = str_replace("../", "", $url);
            return $_L['plugin']['oss']['domain'] . $url;
        }
    }
    return $url;
}
/**
 * @description: 获取服务器信息
 * @param {*}
 * @return array
 */
function server_info()
{
    $serverinfo['os']        = php_uname('s');
    $serverinfo['sys']       = $_SERVER["SERVER_SOFTWARE"];
    $serverinfo['php']       = PHP_VERSION;
    $serverinfo['mysql']     = "Mysql " . DB::$mysql->version();
    $serverinfo['redis']     = !class_exists("Redis") ? false : true;
    $serverinfo['memcached'] = !class_exists("Memcached") ? false : true;
    return $serverinfo;
}
/**
 * 兼容PHP老版本
 */
if (!function_exists('array_key_first')) {
    /**
     * @description: 获取数组第一个键
     * @param array $array
     * @return string|null
     */
    function array_key_first($array = array())
    {
        if (count($array)) {
            reset($array);
            return key($array);
        }
        return null;
    }
}
