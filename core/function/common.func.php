<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2023-03-29 11:05:19
 * @Description: 全局方法
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * @description: 输出字符串或数组
 * @param mixed $vars
 * @param bool $type
 * @return string
 */
function dump($vars, $type = false)
{
    echo "<pre>\n";
    if ($type) {
        var_dump($vars);
    } else {
        echo htmlspecialchars(print_r($vars, true), ENT_COMPAT, 'ISO-8859-1');
    }
    echo "</pre>\n";
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
 * @param string|array $msg 返回提示
 * @param string $go 跳转链接,可选
 * @param mixed $data 输出的数据,可选
 * @return string
 */
function ajaxout($code = 1, $msg = "", $go = "", $data = "")
{
    $arr = [
        "code" => $code,
        "msg"  => $msg,
        "go"   => $go,
        "data" => $data,
    ];
    header("content-type: application/json");
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
 * @return array
 */
function sql2arr($data = "")
{
    if (is_serialize($data)) {
        $result = unserialize($data, [
            'allowed_classes' => false,
        ]);
        if ($result === false) {
            $cache = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
                return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
            }, $data);
            $result = unserialize($cache);
        }
    } elseif (is_array($data)) {
        $result = $data;
    }
    return $result ?: [];
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
 * @param int $time
 * @param string $win
 * @param bool $return
 * @return {*}
 */
function okinfo($url, $time = 0, $win = "window", $return = false)
{
    $url = "/public/static/loading/index.html?v=20221230#go=" . urlencode($url) . "&time={$time}&win={$win}";
    if ($return) {
        return $url;
    }
    goheader($url);
}
/**
 * @description: 302跳转
 * @param string $url
 * @return {*}
 */
function goheader($url)
{
    header("Location: {$url}");
    exit;
}
/**
 * @description: 编码转换
 * @param mixed $str
 * @return mixed
 */
function gbk2utf8($mixed = "")
{
    return mb_convert_encoding($mixed, "UTF-8", "GBK, GB2312, BIG5, ASCII");
}
/**
 * @description: 编码转换
 * @param mixed $str
 * @return mixed
 */
function utf82gbk($mixed = "")
{
    return mb_convert_encoding($mixed, "GBK", "UTF-8");
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
            "_", "_", "/*", "&#92;", "&#34;", "&#39;", "sel/ect", "ins/ert", "up/date", "del/ete", "un/ion", "in/to", "load/_file", "out/file", "sl/eep",
        ], $string);
        if (!is_url($string)) {
            $string = trim(htmlspecialchars($string));
        }
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
 * @param string|array $needle
 * @return bool
 */
function is_mobile($needle = [])
{
    if (is_pad()) {
        return false;
    }
    $needle = is_array($needle) ? $needle : [$needle];
    $needle = array_merge([
        "mobile", "mobi", "wap", "android", "iphone",
    ], $needle);
    return in_ua($needle);
}
/**
 * @description: 检测是否平板端
 * @param string|array $needle
 * @return bool
 */
function is_pad($needle = [])
{
    $needle = is_array($needle) ? $needle : [$needle];
    $needle = array_merge([
        "ipad", "tablet",
    ], $needle);
    return in_ua($needle);
}
/**
 * @description: 检测是否PC端
 * @param string|array $needle
 * @return bool
 */
function is_pc($needle = [])
{
    if (is_mobile()) {
        return false;
    }
    if (is_pad()) {
        return false;
    }
    return true;
}
/**
 * @description: 判断是否为base64编码
 * @param string $str
 * @return bool
 */
function is_base64($str = "")
{
    return $str === base64_encode(base64_decode($str)) ? true : false;
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
function is_serialize($data = "")
{
    if (empty($data)) {
        return false;
    }
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
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
 * @description: 验证是否为URL
 * @param string $url
 * @return bool
 */
function is_url($url = "")
{
    if (parse_url($url, PHP_URL_HOST) !== null) {
        return true;
    }
}
/**
 * @description: 验证是否为邮箱
 * @param string $str
 * @return bool
 */
function is_email($str = "")
{
    if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
}
/**
 * @description: 验证是否为IP地址
 * @param string $str
 * @return bool
 */
function is_ip($str = "")
{
    if (filter_var($str, FILTER_VALIDATE_IP)) {
        return true;
    }
}
/**
 * @description: 判断字符串是否包含
 * @param string $string
 * @param string|array $needle
 * @return bool
 */
function in_string($string = "", $needle = [])
{
    $needle = is_array($needle) ? $needle : [$needle];
    foreach ($needle as $val) {
        if (stripos($string, $val) !== false) {
            return true;
        }
    }
    return false;
}
/**
 * @description: 判断UA中是否包含
 * @param string|array $needle
 * @return bool
 */
function in_ua($needle = [])
{
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        return in_string($_SERVER['HTTP_USER_AGENT'], $needle);
    }
    return false;
}
/**
 * @description: HTML内容解码懒加载
 * @param string $content
 * @param bool $lazyload
 * @param bool $watermark
 * @return string
 */
function html_editor($content = "", $lazyload = false)
{
    if ($content) {
        $content = is_base64($content) ? base64_decode($content) : $content;
        preg_match_all("/<img.*?src=['\"](.*?)['\"].*?[\/]?>/i", $content, $match);
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
 * @param bool $watermark
 * @param int $x
 * @param int $y
 * @return string
 */
function oss($url = "", $watermark = true)
{
    global $_L;
    $cfgoss = $_L['plugin']['oss'] ?: [];
    $cfgwat = $_L['plugin']['watermark'] ?: [];
    $config = $_L['config']['admin'] ?: [];
    $webp   = $config['attwebp'] > 0 ? true : false;
    $cfgwat = array_merge($cfgwat, [
        "on"   => $cfgwat['on'] > 0 && $watermark && !in_string($url, ".gif") ? true : false,
        "text" => base64_encode($cfgwat['text']),
    ]);
    //如果开启云存储
    if ($url && $cfgoss['type'] != "local") {
        //删除链接参数
        $url = explode("?", $url)[0];
        //本地链接转远程链接
        if (!is_url($url) && in_string($url, "../upload/")) {
            $url = str_replace("../", $cfgoss['domain'], $url);
        }
        //如果是图片进一步处理
        if (in_string($url, [
            ".jpg", ".jpeg", ".png", ".gif", ".webp",
        ]) && in_string($url, $cfgoss['domain'])) {
            switch ($cfgoss['type']) {
                case 'qiniu':
                    $url .= "?imageMogr2/interlace/1/quality/75";
                    $url .= $webp ? "/format/webp" : "";
                    $url .= $cfgwat['on'] ? "|watermark/2/text/{$cfgwat['text']}/font/6buR5L2T/fontsize/" . ($cfgwat['size'] * 20) . "/fill" . "/" . base64_encode($cfgwat['fill']) . "/dissolve/{$cfgwat['dissolve']}/gravity/{$cfgwat['gravity']}/dx/{$cfgwat['dx']}/dy/{$cfgwat['dy']}" : "";
                    break;
                case 'tencent':
                    $url .= "?imageMogr2/interlace/1/quality/75";
                    $url .= $webp ? "/format/webp" : "";
                    $url .= $cfgwat['on'] ? "|watermark/2/text/{$cfgwat['text']}/font/c2ltaGVp6buR5L2TLnR0Zg/fontsize/{$cfgwat['size']}/fill" . "/" . base64_encode($cfgwat['fill']) . "/dissolve/{$cfgwat['dissolve']}/shadow/{$cfgwat['shadow']}/gravity/" . (strtolower($cfgwat['gravity'])) . "/dx/{$cfgwat['dx']}/dy/{$cfgwat['dy']}" : "";
                    break;
                case 'aliyun':
                    switch ($cfgwat['gravity']) {
                        case 'NorthWest':
                            $cfgwat['gravity'] = "nw";
                            break;
                        case 'NorthEast':
                            $cfgwat['gravity'] = "ne";
                            break;
                        case 'SouthWest':
                            $cfgwat['gravity'] = "sw";
                            break;
                        case 'SouthEast':
                            $cfgwat['gravity'] = "se";
                            break;
                    }
                    $url .= "?x-oss-process=image/auto-orient,1/interlace,1/quality,q_75";
                    $url .= $webp ? "/format,webp" : "";
                    $url .= $cfgwat['on'] ? "/watermark,text_{$cfgwat['text']},type_d3F5LW1pY3JvaGVp,color_" . str_replace("#", "", $cfgwat['fill']) . ",size_{$cfgwat['size']},shadow_{$cfgwat['shadow']},g_" . (strtolower($cfgwat['gravity'])) . ",x_{$cfgwat['dx']},y_{$cfgwat['dy']}" : "";
                    break;
            }
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
    $serverinfo['os']    = php_uname('s');
    $serverinfo['sys']   = $_SERVER["SERVER_SOFTWARE"];
    $serverinfo['php']   = PHP_VERSION;
    $serverinfo['mysql'] = "Mysql " . DB::$mysql->version();
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
