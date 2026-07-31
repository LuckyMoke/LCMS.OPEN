<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2026-07-25 12:30:23
 * @Description: 全局方法
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * @description: 输出字符串或数组
 * @param array $texts
 * @return string
 */
function dump(...$texts)
{
    $type = array_pop($texts);
    if (is_bool($type)) {
        if ($texts) {
            if ($type) {
                foreach ($texts as $text) {
                    echo "<pre>\n";
                    var_dump($text);
                    echo "</pre>\n";
                }
                return;
            }
        } else {
            $texts[] = $type;
        }
    } elseif ($type) {
        $texts[] = $type;
    }
    foreach ($texts as $text) {
        echo "<pre>\n";
        echo htmlspecialchars(print_r($text, true), ENT_COMPAT, "ISO-8859-1");
        echo "</pre>\n";
    }
    return;
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
 * @param array $params [code, msg, go, data, ...]
 * @return string
 */
function ajaxout(...$params)
{
    if (is_array($params[0])) {
        $params = $params[0];
    } else {
        $params = [
            "code" => $params[0],
            "msg"  => $params[1] ?: "",
            "go"   => $params[2] ?: "",
            "data" => $params[3] ?: "",
        ];
    }
    header("content-type: application/json");
    echo json_encode_ex($params);
    exit;
}
/**
 * @description: 数组序列化
 * @param array $old
 * @param array $new
 * @param string $unarr [a/b/c/... 支持多级]
 * @return string
 */
function arr2sql($old = [], $new = [], $unarr = "")
{
    $old = sql2arr($old);
    if (!is_array($old)) {
        $old = [];
    }
    if (!is_array($new)) {
        $new = [];
    }
    if (!empty($unarr) && $old) {
        $unarr = explode("|", $unarr);
        foreach ($unarr as $pathStr) {
            if (empty($pathStr)) {
                continue;
            }
            $keys = explode("/", $pathStr);
            $temp = &$old;
            foreach ($keys as $i => $key) {
                $key = trim($key);
                if ($key === "") {
                    break;
                }
                if (!is_array($temp) || !array_key_exists($key, $temp)) {
                    break;
                }
                if ($i === count($keys) - 1) {
                    unset($temp[$key]);
                } else {
                    $temp = &$temp[$key];
                }
            }
            unset($temp);
        }
    }
    if ($old || $new) {
        $result = array_replace_recursive($old, $new);
    } else {
        $result = [];
    }
    $sql = serialize($result);
    return is_serialize($sql) ? $sql : "";
}
/**
 * @description: 反序列化
 * @param mixed $data
 * @return array
 */
function sql2arr($data = "")
{
    $result = [];
    if (is_serialize($data)) {
        $result = @unserialize($data, [
            "allowed_classes" => false,
        ]);
        if ($result === false && $data !== "b:0;") {
            $cache = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
                return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
            }, $data);
            $result = @unserialize($cache, [
                "allowed_classes" => false,
            ]);
        }
    } elseif (is_array($data)) {
        $result = $data;
    }
    return is_array($result) ? $result : [];
}
/**
 * @description: 获取当前时间
 * @param string $format
 * @return string
 */
function datenow($format = "Y-m-d H:i:s")
{
    static $cache = [];
    if (!isset($cache[$format])) {
        $cache[$format] = date($format);
    }
    return $cache[$format];
}
/**
 * @description: 时间的转换、截取
 * @param string|int $date
 * @param string $type
 * @return string
 */
function datetime($date, $type = "")
{
    if (empty($date) && $date !== 0 && $date !== "0") {
        return "";
    }
    if (is_numeric($date)) {
        $timestamp = (int) $date;
    } else {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            $timestamp = 0;
        }
    }
    switch ($type) {
        case 'date':
            $format = "Y-m-d";
            break;
        case 'time':
            $format = "H:i:s";
            break;
        case 'y':
            $format = "Y";
            break;
        case 'h':
            $format = "H";
            break;
        case 'datetime':
        case '':
            $format = "Y-m-d H:i:s";
            break;
        default:
            $format = $type;
            break;
    }
    return date($format, $timestamp);
}
/**
 * @description: 正常时间转unix时间戳
 * @param string $date
 * @return int
 */
function unixtime($date)
{
    if (is_numeric($date)) {
        return (int) $date;
    }
    if (empty($date) && $date !== "0" && $date !== 0) {
        return 0;
    }
    $timestamp = @strtotime($date);
    return $timestamp === false ? 0 : $timestamp;
}
/**
 * @description: 获取毫秒时间戳
 * @param {*}
 * @return int
 */
function microseconds()
{
    $microtime = microtime();
    $usec      = substr($microtime, 2, 6);
    return str_pad($usec, 6, "0", STR_PAD_LEFT);
}
/**
 * @description: 获取随机字符串
 * @param int $length 获取长度
 * @param string $type 获取类型:all,num,let
 * @return string
 */
function randstr($length = 4, $type = "all")
{
    $length = (int) $length;
    if ($length < 1) {
        return "";
    }
    switch ($type) {
        case 'num':
        case 'number':
            $str = "0123456789";
            break;
        case 'let':
        case 'letter':
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz";
            break;
        case 'all':
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz23456789";
            break;
        default:
            $str = (string) $type;
            break;
    }
    if ($str === "") {
        return "";
    }
    $maxIndex = strlen($str) - 1;
    $result   = "";
    for ($i = 0; $i < $length; $i++) {
        $result .= $str[random_int(0, $maxIndex)];
    }
    return $result;
}
/**
 * @description: 生成随机KEY
 * @param int $bit
 * @return string
 */
function randkey($bit = 256)
{
    $key = random_bytes(intval($bit / 8));
    return urlsafe_base64_encode($key);
}
/**
 * @description: 将字节转换为带单位的字符串
 * @param int $size
 * @param string $unit
 * @return string
 */
function getunit($size, $unit = null)
{
    $size    = (float) $size;
    $base    = 1024;
    $unitMap = [
        "B" => 0,
        "K" => 1, "KB" => 1,
        "M" => 2, "MB" => 2,
        "G" => 3, "GB" => 3,
        "T" => 4, "TB" => 4,
        "P" => 5, "PB" => 5,
    ];
    $standardUnits = [
        "B", "KB", "MB", "GB", "TB", "PB",
    ];
    $unitIndex = 0;
    if ($unit !== null) {
        $unit = strtoupper(trim($unit));
        if (isset($unitMap[$unit])) {
            $unitIndex = $unitMap[$unit];
            $size /= pow($base, $unitIndex);
        } else {
            $unitIndex = 0;
        }
    } else {
        while ($size >= $base && $unitIndex < count($standardUnits) - 1) {
            $size /= $base;
            $unitIndex++;
        }
    }

    $outputUnit = $standardUnits[$unitIndex];
    return sprintf("%.2f%s", $size, $outputUnit);
}
/**
 * @description: 带单位的字符串转换为字节
 * @param string $size
 * @return int
 */
function getbytes($size)
{
    if (is_numeric($size)) {
        return (int) $size;
    }
    $size = trim($size);

    if (!preg_match('/^([\d.]+)\s*([BKMGTPE]?B?)$/i', $size, $matches)) {
        return 0;
    }
    $num     = (float) $matches[1];
    $unit    = strtoupper($matches[2]);
    $unitMap = [
        "B"  => 1,
        "KB" => 1024,
        "MB" => 1024 ** 2,
        "GB" => 1024 ** 3,
        "TB" => 1024 ** 4,
        "PB" => 1024 ** 5,
        "K"  => 1024,
        "M"  => 1024 ** 2,
        "G"  => 1024 ** 3,
        "T"  => 1024 ** 4,
        "P"  => 1024 ** 5,
    ];
    $multiplier = $unitMap[$unit] ?? 1;
    $bytes      = $num * $multiplier;
    if (is_infinite($bytes) || $bytes > PHP_INT_MAX) {
        return $bytes;
    }
    return (int) round($num * $multiplier);
}
/**
 * @description: 获取两数之间的随机数，含小数
 * @param float|int $min 最小值
 * @param float|int $max 最大值
 * @param int $pre 保留小数位数
 * @return float
 */
function randfloat($min = 0, $max = 1, $pre = 2)
{
    if ($min > $max) {
        [$min, $max] = [$max, $min];
    }
    if ($min == $max) {
        return round($min, $pre);
    }
    $randomUnit = mt_rand() / mt_getrandmax();
    return round($min + $randomUnit * ($max - $min), $pre);
}
/**
 * @description: 清理URL中的参数
 * @param string $url
 * @param string|array $keys 支持传入 "a|b" 或 ["a", "b"]
 * @return string
 */
function url_clear($url, $keys)
{
    if (empty($url)) {
        return "";
    }
    if (is_string($keys)) {
        $keys = explode("|", $keys);
    }
    $keys = array_filter($keys);
    $urls = parse_url($url);
    if ($urls === false) {
        return $url;
    }
    $queryArr = [];
    if (isset($urls['query'])) {
        parse_str($urls['query'], $queryArr);
    }
    foreach ($keys as $key) {
        unset($queryArr[$key]);
    }
    $qry    = http_build_query($queryArr);
    $result = "";
    if (isset($urls['scheme'])) {
        $result .= $urls['scheme'] . (isset($urls['host']) ? "://" : ":");
    }
    if (isset($urls['user'])) {
        $result .= $urls['user'];
        if (isset($urls['pass'])) {
            $result .= ":" . $urls['pass'];
        }
        $result .= "@";
    }
    if (isset($urls['host'])) {
        $result .= $urls['host'];
    }
    if (isset($urls['port'])) {
        $result .= ":" . $urls['port'];
    }
    if (isset($urls['path'])) {
        $result .= $urls['path'];
    }
    if (!empty($qry)) {
        $result .= "?" . $qry;
    }
    if (isset($urls['fragment'])) {
        $result .= "#" . $urls['fragment'];
    }
    return $result;
}
/**
 * @description: URL去除scheme
 * @param string $url
 * @return string
 */
function url_auto($url = "")
{
    if (empty($url)) {
        return "";
    }
    $url = trim($url);
    return preg_replace('#^https?://#i', '//', $url);
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
    $url = "/public/static/loading/index.html?v=20240615#go=" . urlencode($url) . "&time={$time}&win={$win}";
    if ($return) {
        return $url;
    }
    goheader($url);
}
/**
 * @description: JS表单跳转
 * @param string $url
 * @param array $form
 * @param int $time
 * @param string $other
 * @return {*}
 */
function okform($url, $form = [], $time = 0, $other = "")
{
    $html  = file_get_contents(PATH_PUBLIC . "static/loading/index.html");
    $input = "";
    foreach ($form as $name => $val) {
        $input .= '<input type="hidden" name="' . $name . '" value="' . $val . '" />';
    }
    $html = str_replace([
        "<!--okform-->", "/**okform*/",
    ], [
        '<form id="form" method="POST" action="' . $url . '">' . $input . '<button id="bclick" type"submit" style="display:none;margin-top:20px;font-size:12px;color:#47a1ff;border:none;background:none;cursor:pointer;outline:none;text-decoration:underline">点击跳转</button></form>' . $other,
        "times=time={$time};",
    ], $html);
    exit($html);
}
/**
 * @description: 302跳转
 * @param string $url
 * @return {*}
 */
function goheader($url = "")
{
    if (empty($url)) {
        return;
    }
    $url = str_replace(["\r", "\n", "%0d", "%0a"], "", $url);
    header("Location: {$url}", true, 302);
    exit;
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function gbk2utf8($str = "")
{
    if ($str === "") {
        return "";
    }
    $encoding = mb_detect_encoding($str, [
        "ASCII", "UTF-8", "GB2312", "GBK", "BIG5",
    ], true);
    if ($encoding === 'UTF-8' || $encoding === 'ASCII') {
        return $str;
    }
    return mb_convert_encoding($str, "UTF-8", "GBK, GB2312, BIG5");
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function utf82gbk($str = "")
{
    if ($str === "") {
        return "";
    }
    $encoding = mb_detect_encoding($str, [
        "ASCII", "UTF-8", "GB2312", "GBK", "BIG5",
    ], true);
    if ($encoding === 'UTF-8') {
        return mb_convert_encoding($str, "GBK", "UTF-8");
    }
    return $str;
}
/**
 * @description: 字符串过滤
 * @param array|string $string
 * @return array|string
 */
/**
 * @description: 字符串过滤 (黑名单替换 + 实体转义)
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
        static $filters = [
            "\\"                 => "&#92;",
            "*"                  => "&#42;",
            "%5C"                => "&#37;&#53;&#67;",
            "%22"                => "&#37;&#50;&#50;",
            "%27"                => "&#37;&#50;&#55;",
            "create"             => "&#99;&#114;&#101;&#97;&#116;&#101;",
            "select"             => "&#115;&#101;&#108;&#101;&#99;&#116;",
            "insert"             => "&#105;&#110;&#115;&#101;&#114;&#116;",
            "update"             => "&#117;&#112;&#100;&#97;&#116;&#101;",
            "delete"             => "&#100;&#101;&#108;&#101;&#116;&#101;",
            "union"              => "&#117;&#110;&#105;&#111;&#110;",
            "into"               => "&#105;&#110;&#116;&#111;",
            "alter"              => "&#97;&#108;&#116;&#101;&#114;",
            "truncate"           => "&#116;&#114;&#117;&#110;&#99;&#97;&#116;&#101;",
            "drop"               => "&#100;&#114;&#111;&#112;",
            "exec"               => "&#101;&#120;&#101;&#99;",
            "execute"            => "&#101;&#120;&#101;&#99;&#117;&#116;&#101;",
            "sleep"              => "&#115;&#108;&#101;&#101;&#112;",
            "benchmark"          => "&#98;&#101;&#110;&#99;&#104;&#109;&#97;&#114;&#107;",
            "load_file"          => "&#108;&#111;&#97;&#100;&#95;&#102;&#105;&#108;&#101;",
            "outfile"            => "&#111;&#117;&#116;&#102;&#105;&#108;&#101;",
            "extractvalue"       => "&#101;&#120;&#116;&#114;&#97;&#99;&#116;&#118;&#97;&#108;&#117;&#101;",
            "updatexml"          => "&#117;&#112;&#100;&#97;&#116;&#101;&#120;&#109;&#108;",
            "substr"             => "&#115;&#117;&#98;&#115;&#116;&#114;",
            "substring"          => "&#115;&#117;&#98;&#115;&#116;&#114;&#105;&#110;&#103;",
            "concat"             => "&#99;&#111;&#110;&#99;&#97;&#116;",
            "information_schema" => "&#105;&#110;&#102;&#111;&#114;&#109;&#97;&#116;&#105;&#111;&#110;&#95;&#115;&#99;&#104;&#101;&#109;&#97;",
            "xp_cmdshell"        => "&#120;&#112;&#95;&#99;&#109;&#100;&#115;&#104;&#101;&#108;&#108;",
            "waitfor"            => "&#119;&#97;&#105;&#116;&#102;&#111;&#114;",
            "/script"            => "&#47;&#115;&#99;&#114;&#105;&#112;&#116;",
            "script"             => "&#115;&#99;&#114;&#105;&#112;&#116;",
            "javascript"         => "&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;",
            "vbscript"           => "&#118;&#98;&#115;&#99;&#114;&#105;&#112;&#116;",
            "eval"               => "&#101;&#118;&#97;&#108;",
            "document"           => "&#100;&#111;&#99;&#117;&#109;&#101;&#110;&#116;",
        ];
        if (!is_url($string)) {
            $string = htmlspecialchars($string, ENT_QUOTES, "UTF-8");
        }
        $string = str_ireplace(array_keys($filters), array_values($filters), $string);
        $string = trim($string);
    }
    return $string;
}
/**
 * @description: 全局表单内容过滤
 * @param array|string $string
 * @return array|string
 */
function filterform($string = null)
{
    if (!is_array($string) && !is_string($string)) {
        return $string;
    }
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            if (is_string($key) && !preg_match("/^[a-zA-Z0-9_-]*$/", $key)) {
                unset($string[$key]);
                continue;
            }
            $string[$key] = filterform($val);
        }
    } elseif ($string !== "") {
        $module = defined('L_MODULE')  ?L_MODULE : "";
        if ($module != "admin") {
            if (!preg_match("/^[a-zA-Z0-9_-]+$/", $string)) {
                $string = sqlinsert($string);
            }
        } else {
            $string = trim($string);
        }
    }
    return $string;
}
/**
 * @description: 剔除所有4字节字符
 * @param string $str
 * @return string
 */
function filter4byte($str)
{
    if (!is_string($str) || $str === "") {
        return $str;
    }
    return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $str);
}
/**
 * @description: 人民币小写转大写
 * @param string|float|int $rmb
 * @return string
 */
function rmb($rmb)
{
    $rmb   = sprintf('%.2f', round((float) $rmb, 2));
    $cnums = [
        "0" => "零",
        "1" => "壹",
        "2" => "贰",
        "3" => "叁",
        "4" => "肆",
        "5" => "伍",
        "6" => "陆",
        "7" => "柒",
        "8" => "捌",
        "9" => "玖",
    ];
    $cnyunits        = ["圆", "角", "分"];
    $grees           = ["拾", "佰", "仟", "万", "拾", "佰", "仟", "亿"];
    list($ns1, $ns2) = explode(".", $rmb, 2);
    $ns2_arr         = array_filter([
        $ns2[1], $ns2[0],
    ], function ($v) {
        return $v !== "0";
    });
    $int_part = implode("", _rmb_map(str_split($ns1), $grees));
    $ret      = array_merge($ns2_arr, [
        $int_part, "",
    ]);
    $ret = implode("", array_reverse(_rmb_map($ret, $cnyunits)));
    $ret = str_replace(array_keys($cnums), array_values($cnums), $ret);
    $ret = preg_replace([
        "/零+/", "/零圆/",
    ], [
        "零", "圆",
    ], $ret);
    if (
        strpos($ret, "角") === false &&
        strpos($ret, '分') === false
    ) {
        $ret .= "整";
    }
    return $ret;
}
function _rmb_map($list, $units)
{
    $ul = count($units);
    $xs = [];
    foreach (array_reverse($list) as $x) {
        $l = count($xs);
        if ($x != "0" || !($l % 4)) {
            $unit_key = ($l - 1) % $ul;
            $unit     = ($l > 0) ? $units[$unit_key] : "";
            $n        = ($x == "0" ? "" : $x) . $unit;
        } else {
            $n = (!empty($xs) && is_numeric($xs[0][0])) ? $x : "";
        }
        array_unshift($xs, $n);
    }
    return $xs;
}
/**
 * @description: 字符串截取
 * @param string $str
 * @param int $start
 * @param int|null $length null表示截取到末尾
 * @return string
 */
function strcut($str = "", $start = 0, $length = null)
{
    if ($str === "") {
        return "";
    }
    return mb_substr($str, $start, $length, "UTF-8");
}
/**
 * @description: 字符串模糊 (脱敏打星号)
 * @param string $str
 * @param int $start 头部保留的字符数
 * @param int $end 尾部保留的字符数
 * @return string
 */
function strstar($str = "", $start = 1, $end = 0)
{
    if ($str === "") {
        return "";
    }
    $len = mb_strlen($str, "UTF-8");
    if ($start < 0) {
        $start = max(0, $len + $start);
    }
    if ($end < 0) {
        $end = max(0, $len + $end);
    }
    if ($start >= $len) {
        $start = $len;
    }
    if ($end >= $len) {
        $end = $len;
    }
    if ($start + $end >= $len) {
        return $str;
    }
    $top     = mb_substr($str, 0, $start, "UTF-8");
    $bottom  = $end > 0 ? mb_substr($str, -$end, null, "UTF-8") : "";
    $maskLen = $len - $start - $end;
    $mask    = str_repeat('*', $maskLen);
    return $top . $mask . $bottom;
}
/**
 * @description: 获取转义后的域名
 * @param string $host
 * @return string
 */
function realhost($host = "")
{
    if (empty($host)) {
        return "";
    }
    $host = str_replace([
        "。", "：",
    ], [
        ".", ":",
    ], $host);
    static $punycode = null;
    if ($punycode === null) {
        require_once PATH_CORE_PLUGIN . "Punycode/Punycode.php";
        $punycode = new PQCMS\Punycode();
    }
    $isUrl = preg_match("#^[a-zA-Z][a-zA-Z0-9+\-.]*://#", $host);
    if ($isUrl) {
        $parts = parse_url($host);
        if (
            $parts === false ||
            !isset($parts['host']) ||
            $parts['host'] === ""
        ) {
            return $host;
        }
        $originalHost = $parts['host'];
        $encodedHost  = $punycode->encode(mb_strtolower($originalHost, "UTF-8"));
        if (
            $encodedHost === false ||
            $encodedHost === $originalHost
        ) {
            return $host;
        }
        $pattern = "#(^[a-zA-Z][a-zA-Z0-9+\-.]*://)" . preg_quote($originalHost, "#") . "#i";
        return preg_replace($pattern, "$1" . $encodedHost, $host, 1);
    } else {
        $encodedHost = $punycode->encode(mb_strtolower($host, 'UTF-8'));
        return ($encodedHost === false) ? $host : $encodedHost;
    }
}
/**
 * @description: 获取根域名
 * @param {*} $domain
 * @return {*}
 */
function roothost($domain = "")
{
    $domain = trim((string) $domain);
    if (strpos($domain, '://') !== false) {
        $parsed = parse_url($domain);
        if (
            $parsed === false ||
            !isset($parsed['host'])
        ) {
            return "";
        }
        $domain = $parsed['host'];
    }
    $domain = strtolower(strtok($domain, ":"));
    if (empty($domain)) return $domain;
    if (
        is_ip($domain) ||
        $domain === "localhost" ||
        strpos($domain, ".") === false
    ) {
        return realhost($domain);
    }
    static $rules = null;
    if ($rules == null) {
        $rules = file_get_contents(PATH_CORE . "pqfile/roothost.pqdat");
        $rules = json_decode($rules, true);
    }
    $parts         = explode(".", $domain);
    $suffix        = "";
    $currentSuffix = "";
    for ($i = count($parts) - 1; $i >= 0; $i--) {
        $currentSuffix  = ($currentSuffix === "") ? $parts[$i] : $parts[$i] . "." . $currentSuffix;
        $wildcardDomain = "";
        if ($i < count($parts) - 1) {
            $wildcardDomain = "*." . implode(".", array_slice($parts, $i + 1));
        }
        if (isset($rules["!" . $currentSuffix])) {
            $suffix = implode(".", array_slice($parts, $i + 1));
            break;
        }
        if (isset($rules[$currentSuffix]) || ($wildcardDomain !== "" && isset($rules[$wildcardDomain]))) {
            $suffix = $currentSuffix;
        }
    }
    if ($suffix === "") {
        $suffix = end($parts);
    }
    $suffixParts = explode(".", $suffix);
    $suffixCount = count($suffixParts);
    $rootCount   = $suffixCount + 1;
    if (count($parts) < $rootCount) {
        return realhost($domain);
    }
    $rootParts = array_slice($parts, -$rootCount);
    return realhost(implode(".", $rootParts));
}
function _roothost_rules()
{
    $data = HTTP::request([
        "method" => "GET",
        "url"    => "https://publicsuffix.org/list/public_suffix_list.dat",
    ]);
    if (!$data) ajaxout(0, "获取根域名规则失败");
    $rules = [];
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $line = trim($line);
        if (
            empty($line) ||
            strpos($line, "//") === 0
        ) {
            continue;
        }
        $rules[$line] = true;
    }
    file_put_contents(PATH_CORE . "pqfile/roothost.pqdat", json_encode($rules));
}
/**
 * @description: AES字符串加密
 * @param string $string
 * @param string $token
 * @return string
 */
function ssl_encode($string, $token = "LCMS")
{

    $encrypt = openssl_encrypt($string, "AES-128-ECB", $token, OPENSSL_RAW_DATA);
    $encrypt = strtolower(bin2hex($encrypt));
    return $encrypt;
}
/**
 * @description: AES字符串解密
 * @param string $string
 * @param string $token
 * @return string
 */
function ssl_decode($string, $token = "LCMS")
{
    $decrypted = openssl_decrypt(hex2bin($string), "AES-128-ECB", $token, OPENSSL_RAW_DATA);
    return $decrypted;
}
/**
 * @description: AES字符串加密并压缩
 * @param string $string
 * @param string $token
 * @return string
 */
function ssl_encode_gzip($string, $token = "LCMS")
{
    $encrypt = openssl_encrypt($string, "AES-256-CBC", $token, OPENSSL_RAW_DATA);
    $encrypt = gzcompress($encrypt, 9);
    $encrypt = urlsafe_base64_encode($encrypt);
    return $encrypt;
}
/**
 * @description: AES字符串解压并解密
 * @param string $string
 * @param string $token
 * @return string
 */
function ssl_decode_gzip($string, $token = "LCMS")
{
    $decrypted = urlsafe_base64_decode($string);
    if (!$decrypted) {
        return "";
    }
    $decrypted = gzuncompress($decrypted);
    if (!$decrypted) {
        return "";
    }
    $decrypted = openssl_decrypt($decrypted, "AES-256-CBC", $token, OPENSSL_RAW_DATA);
    return $decrypted;
}
/**
 * @description: 生成公钥私钥
 * @param int $bit
 * @return array|bool
 */
function rsa_create($bit = 2048)
{
    global $_L;
    $options = [
        "digest_alg"       => "sha256",
        "private_key_bits" => $bit,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    if ($_L['openssl']['config']) {
        $options['config'] = $_L['openssl']['config'];
    }
    $res = openssl_pkey_new($options);
    if ($res === false) {
        $crt = PATH_CORE . "rsa.crt";
        if (!file_exists($crt)) return false;
        $crt = file_get_contents($crt);
        $crt = json_decode($crt, true);
        if (!$crt) return false;
        $pubKey  = $crt['pubKey'];
        $privKey = $crt['privKey'];
    } else {
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];
        if (!$privKey || !$pubKey) {
            return false;
        }
    }
    return [
        "pubKey"     => $pubKey,
        "privKey"    => $privKey,
        "pubKeyStr"  => str_replace([
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            " ",
            "\r\n",
            "\n",
        ], "", $pubKey),
        "privKeyStr" => str_replace([
            "-----BEGIN PRIVATE KEY-----",
            "-----END PRIVATE KEY-----",
            " ",
            "\r\n",
            "\n",
        ], "", $privKey),
    ];
}
/**
 * @description: 公钥加密
 * @param string $string
 * @param string $pubKey
 * @return string|bool
 */
function rsa_encode($string, $pubKey)
{
    $pubKey = str_replace([
        "-----BEGIN PUBLIC KEY-----",
        "-----END PUBLIC KEY-----",
        " ",
        "\r\n",
        "\n",
    ], "", $pubKey);
    $pubKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    if (openssl_public_encrypt($string, $encrypted, $pubKey)) {
        return base64_encode($encrypted);
    }
    return false;
}
/**
 * @description: 私钥解密
 * @param string $string
 * @param string $privKey
 * @return string|bool
 */
function rsa_decode($string, $privKey)
{
    $string  = base64_decode($string);
    $privKey = str_replace([
        "-----BEGIN PRIVATE KEY-----",
        "-----END PRIVATE KEY-----",
        " ",
        "\r\n",
        "\n",
    ], "", $privKey);
    $privKey = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($privKey, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
    if (openssl_private_decrypt($string, $decrypted, $privKey)) {
        return $decrypted;
    }
    return false;
}
/**
 * @description: 创建JWT
 * @param array $payload
 * @param string $key
 * @param array $header
 * @return string
 */
function jwt_encode($payload, $key = "LCMS", $header = [])
{
    $header = urlsafe_base64_encode(json_encode($header ?: [
        "alg" => "HS256",
        "typ" => "JWT",
    ]));
    $payload = $header . "." . urlsafe_base64_encode(json_encode($payload));
    $sign    = urlsafe_base64_encode(hash_hmac("sha256", $payload, $key, true));
    return "{$payload}.{$sign}";
}
/**
 * @description: 验证JWT
 * @param string $jwt
 * @param string $key
 * @return array
 */
function jwt_decode($jwt, $key = "LCMS")
{
    list($header, $paylod, $sign) = explode(".", $jwt);
    if (hash_equals(hash_hmac("sha256", "{$header}.{$paylod}", $key, true), urlsafe_base64_decode($sign))) {
        $payload = json_decode(urlsafe_base64_decode($paylod), true);
        return $payload;
    }
    return false;
}
/**
 * @description: 国密SM3加密
 * @param string $string
 * @return {*}
 */
function sm3_encode($string = "")
{
    if (!$string) return false;
    $string = (string) $string;
    $hash   = @openssl_digest($string, "sm3", false);
    return $hash !== false ? $hash : false;
}
/**
 * @description: 国密SM4加密-生成密钥和向量
 * @return {*}
 */
function sm4_create($isbyte = false)
{
    $key = openssl_random_pseudo_bytes(16);
    $iv  = openssl_random_pseudo_bytes(16);
    if ($key === false || $iv === false) {
        return false;
    }
    if ($isbyte) {
        return [
            "key" => $key,
            "iv"  => $iv,
        ];
    } else {
        return [
            "key" => urlsafe_base64_encode($key),
            "iv"  => urlsafe_base64_encode($iv),
        ];
    }
}
/**
 * @description: 国密SM4加密
 * @param string $string
 * @param string $key
 * @param string $iv
 * @return string
 */
function sm4_encode($string, $key, $iv)
{
    $string = (string) $string;
    $key    = urlsafe_base64_decode($key);
    $iv     = urlsafe_base64_decode($iv);
    if (strlen($key) !== 16 || strlen($iv) !== 16) {
        return false;
    }
    $encode = openssl_encrypt($string, "sm4-cbc", $key, OPENSSL_RAW_DATA, $iv);
    if ($encode === false) {
        return false;
    }
    return urlsafe_base64_encode($encode);
}
/**
 * @description: 国密SM4解密
 * @param string $string
 * @param string $key
 * @param string $iv
 * @return string
 */
function sm4_decode($string, $key, $iv)
{
    $string = urlsafe_base64_decode((string) $string);
    $key    = urlsafe_base64_decode($key);
    $iv     = urlsafe_base64_decode($iv);
    if (strlen($key) !== 16 || strlen($iv) !== 16 || $string === false) {
        return false;
    }
    $decode = openssl_decrypt($string, "sm4-cbc", $key, OPENSSL_RAW_DATA, $iv);
    return $decode !== false ? $decode : false;
}
/**
 * @description: 创建UUID-V7
 * @return string
 */
function uuid_create()
{
    $time         = (int) (microtime(true) * 1000);
    $timeHex      = sprintf('%012x', $time);
    $randBytes    = random_bytes(10);
    $randBytes[6] = chr((ord($randBytes[6]) & 0x0F) | 0x70);
    $randBytes[7] = chr((ord($randBytes[7]) & 0x3F) | 0x80);
    $randHex      = bin2hex($randBytes);
    $hex          = $timeHex . $randHex;
    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}
/**
 * @description: 替换富文本中图片懒加载
 * @param string $str
 * @return string
 */
function lazyload($str = "")
{
    if (empty($str)) {
        return $str;
    }
    $pattern = '/<(img|iframe|video)\b([^>]*?)(\/?)>/is';
    return preg_replace_callback($pattern, function ($matches) {
        $tag   = $matches[1];
        $attrs = $matches[2];
        $end   = $matches[3];
        if (!preg_match('/\sdata-src\s*=/i', $attrs)) {
            $attrs = preg_replace('/(\s)src(\s*=)/i', "$1data-src$2", $attrs);
        }
        switch ($tag) {
            case 'img':
                $attrs = _lazyload_attr($attrs, "class", "lazyload");
                break;
            case 'iframe':
                $attrs = _lazyload_attr($attrs, "frameborder", "0", true);
                $attrs = _lazyload_attr($attrs, "border", "0", true);
                break;
            case 'video':
                $attrs = _lazyload_attr($attrs, "class", "video-js");
                break;
        }
        return "<" . $tag . $attrs . $end . ">";
    }, $str);
}
function _lazyload_attr($attrs, $name, $value, $force_new = false)
{
    if ($name === 'class' && !$force_new) {
        if (preg_match('/((?:^|\s)class\s*=\s*)(["\'])(.*?)\2/is', $attrs, $classMatch)) {
            if (stripos($classMatch[3], $value) === false) {
                $newClass = trim($classMatch[3] . ' ' . $value);
                $attrs    = preg_replace_callback(
                    '/((?:^|\s)class\s*=\s*)(["\'])' . preg_quote($classMatch[3], '/') . '(\2)/is',
                    function ($m) use ($newClass) {
                        return $m[1] . $m[2] . $newClass . $m[2];
                    },
                    $attrs,
                    1
                );
            }
        } else {
            $attrs .= ' class="' . $value . '"';
        }
    } else {
        $pattern = '/(?:^|\s|<)' . preg_quote($name, '/') . '\s*=/i';
        if (!preg_match($pattern, $attrs)) {
            $attrs .= ' ' . $name . '="' . $value . '"';
        }
    }
    return $attrs;
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
    $needle = array_filter(array_merge([
        "mobile", "mobi", "wap", "android", "iphone",
    ], $needle));
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
    $needle = array_filter(array_merge([
        "ipad", "tablet", "silk",
    ], $needle));
    return in_ua($needle);
}
/**
 * @description: 检测是否PC端
 * @return bool
 */
function is_pc()
{
    return !is_mobile();
}
/**
 * @description: 判断是否为base64编码
 * @param string $str
 * @return bool
 */
function is_base64($str = "")
{
    $str = (string) $str;
    if (empty($str)) {
        return false;
    }
    if (
        !preg_match('/^[a-zA-Z0-9\/\+=]+$/', $str) ||
        strlen($str) % 4 !== 0
    ) {
        return false;
    }
    $decoded = base64_decode($str, true);
    if ($decoded === false) {
        return false;
    }
    return base64_encode($decoded) === $str;
}
/**
 * @description: 判断是否为手机号
 * @param string $phone
 * @return bool
 */
function is_phone($phone = "")
{
    if (!$phone) return false;
    return (bool) preg_match("/^1[3-9]\d{9}$/", (string) $phone);
}
/**
 * @description: 判断是否为序列化
 * @param mixed $data
 * @return boolean
 */
function is_serialize($data = "")
{
    if (!is_string($data) || $data === "") {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
        return false;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (":" !== $data[1]) {
        return false;
    }
    $semicolon = strpos($data, ";");
    $brace     = strpos($data, "}");
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
    return stripos((string) $url, "https://") === 0;
}
/**
 * @description: 验证是否为URL
 * @param string $url
 * @return bool
 */
function is_url($url = "")
{
    $url = trim((string) $url);
    if (empty($url)) {
        return false;
    }
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
/**
 * @description: 验证是否为邮箱
 * @param string $str
 * @return bool
 */
function is_email($str = "")
{
    $str = trim((string) $str);
    if (empty($str)) {
        return false;
    }
    return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;

}
/**
 * @description: 判断是否为IP地址
 * @param string $ip
 * @param string $type [ipv4、ipv6]
 * @return bool
 */
function is_ip($ip = "", $type = "")
{
    if (empty($ip) || $ip == "unknown") {
        return false;
    }
    $flags = [
        "ipv4" => FILTER_FLAG_IPV4,
        "ipv6" => FILTER_FLAG_IPV6,
    ][$type] ?? 0;
    return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
}
/**
 * @description: 判断是否内网IP
 * @param string $ip
 * @return bool
 */
function is_intranet_ip($ip = "")
{
    if (!is_ip($ip)) {
        return false;
    }
    $publicIp = filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    return $publicIp === false;
}
/**
 * @description: 判断字符串是否包含
 * @param string $string
 * @param string|array $needle
 * @return bool
 */
function in_string($string = "", $needle = [])
{
    $string = (string) $string;
    $needle = is_array($needle) ? $needle : [$needle];
    foreach ($needle as $val) {
        $val = (string) $val;
        if ($val !== "" && stripos($string, $val) !== false) {
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
 * @description: url安全的base64_ecnode
 * @param string $str
 * @return string
 */
function urlsafe_base64_encode($str = "")
{
    $str = (string) $str;
    if ($str === "") {
        return "";
    }
    return rtrim(strtr(base64_encode($str), "+/", "-_"), "=");
}
/**
 * @description: url安全的base64_decode
 * @param string $str
 * @return string
 */
function urlsafe_base64_decode($str = "")
{
    $str = (string) $str;
    if ($str === "") {
        return "";
    }
    $data      = strtr($str, "-_", "+/");
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat("=", 4 - $remainder);
    }
    return base64_decode($data, true);
}
/**
 * @description: HTML内容解码懒加载
 * @param string $body
 * @param bool $lazyload
 * @return string
 */
function html_editor($body = "", $lazyload = false)
{
    if (empty($body)) {
        return "";
    }
    if (is_base64($body)) {
        $body = base64_decode($body);
    }
    $body = preg_replace_callback('/<img\b[^>]*>/is', function ($matches) {
        $tag = $matches[0];
        if (preg_match('/(\ssrc\s*=\s*)(["\'])(.*?)\2/i', $tag, $srcMatch)) {
            $originalSrc = $srcMatch[3];
            $newSrc      = oss($originalSrc);
            $tag         = preg_replace_callback(
                '/(\ssrc\s*=\s*)(["\'])' . preg_quote($originalSrc, '/') . '(\2)/i',
                function ($m) use ($newSrc) {
                    return $m[1] . $m[2] . $newSrc . $m[2];
                },
                $tag,
                1
            );
        }
        return $tag;
    }, $body);
    if ($lazyload) {
        $body = lazyload($body);
    }
    $body = preg_replace('/<p\b[^>]*>\s*(<br\s*\/?>)?\s*<\/p>/is', "", $body);
    return $body;
}
/**
 * @description: 获取云存储链接
 * @param string $url
 * @param bool $watermark
 * @return string
 */
function oss($url = "", $watermark = true)
{
    global $_L;
    if (!$url) {
        return "";
    }
    $cfgoss = $_L['plugin']['oss'] ?: [];
    $urls   = explode("|", $url);
    if (count($urls) > 1) {
        foreach ($urls as $index => $val) {
            $urls[$index] = oss($val, $watermark);
        }
        return implode("|", $urls);
    } else {
        $url = $urls[0];
    }
    if ($cfgoss['type'] != "local") {
        $cfgwat = $_L['plugin']['watermark'] ?: [];
        $config = $_L['config']['admin'] ?: [];
        $webp   = $config['attwebp'] > 0  ?true : false;
        $cfgwat = array_merge($cfgwat, [
            "on"   => $cfgwat['on'] > 0 && $watermark && !in_string($url, ".gif")  ?true : false,
            "text" => urlsafe_base64_encode($cfgwat['text']),
        ]);
        //删除链接参数
        $url = explode("?", $url)[0];
        //本地链接转远程链接
        if (!is_url($url) && in_string($url, "../upload/")) {
            $url = $cfgoss['domain'] . ltrim($url, "../");
        }
        //如果是图片进一步处理
        if (preg_match("/[a-zA-Z0-9]\.(jpg|jpeg|png|gif|webp)/i", $url) && in_string($url, $cfgoss['domain'])) {
            switch ($cfgoss['type']) {
                case 'qiniu':
                    $url .= "?imageMogr2/interlace/1";
                    $url .= $webp ? "/format/webp" : "";
                    $url .= $cfgwat['on'] ? "|watermark/2/text/{$cfgwat['text']}/font/V2VuUXVhbllpIE1pY3JvIEhlaQ/fontsize/" . ($cfgwat['size'] * 20) . "/fill" . "/" . urlsafe_base64_encode($cfgwat['fill']) . "/dissolve/{$cfgwat['dissolve']}/gravity/{$cfgwat['gravity']}/dx/{$cfgwat['dx']}/dy/{$cfgwat['dy']}" : "";
                    break;
                case 'tencent':
                    $url .= "?imageMogr2/interlace/1";
                    $url .= $webp ? "/format/webp" : "";
                    $url .= $cfgwat['on'] ? "|watermark/2/text/{$cfgwat['text']}/font/c2ltaGVp6buR5L2TLnR0Zg/fontsize/{$cfgwat['size']}/fill" . "/" . urlsafe_base64_encode($cfgwat['fill']) . "/dissolve/{$cfgwat['dissolve']}/shadow/{$cfgwat['shadow']}/gravity/" . (strtolower($cfgwat['gravity'])) . "/dx/{$cfgwat['dx']}/dy/{$cfgwat['dy']}" : "";
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
                    $url .= "?x-oss-process=image/auto-orient,1/interlace,1";
                    $url .= $webp ? "/format,webp" : "";
                    $url .= $cfgwat['on'] ? "/watermark,text_{$cfgwat['text']},type_d3F5LW1pY3JvaGVp,color_" . str_replace("#", "", $cfgwat['fill']) . ",size_{$cfgwat['size']},shadow_{$cfgwat['shadow']},g_" . (strtolower($cfgwat['gravity'])) . ",x_{$cfgwat['dx']},y_{$cfgwat['dy']}" : "";
                    break;
                case 'baidu':
                    switch ($cfgwat['gravity']) {
                        case 'NorthWest':
                            $cfgwat['gravity'] = 1;
                            break;
                        case 'North':
                            $cfgwat['gravity'] = 2;
                            break;
                        case 'NorthEast':
                            $cfgwat['gravity'] = 3;
                            break;
                        case 'West':
                            $cfgwat['gravity'] = 4;
                            break;
                        case 'Center':
                            $cfgwat['gravity'] = 5;
                            break;
                        case 'East':
                            $cfgwat['gravity'] = 6;
                            break;
                        case 'SouthWest':
                            $cfgwat['gravity'] = 7;
                            break;
                        case 'South':
                            $cfgwat['gravity'] = 8;
                            break;
                        case 'SouthEast':
                            $cfgwat['gravity'] = 9;
                            break;
                    }
                    $url .= "?x-bce-process=image/auto-orient,o_1/interlace,i_progressive";
                    $url .= $webp ? "/format,f_webp" : "";
                    $url .= $cfgwat['on'] ? "/watermark,text_{$cfgwat['text']},type_RlpIZWk,color_" . str_replace("#", "", $cfgwat['fill']) . ",size_{$cfgwat['size']},g_{$cfgwat['gravity']},x_{$cfgwat['dx']},y_{$cfgwat['dy']}" : "";
                    break;
            }
        }
    }
    return $url;
}
/**
 * @description: 获取当前访问协议
 * @return bool
 */
function getscheme()
{
    if (
        !empty($_SERVER['HTTPS']) &&
        strtolower($_SERVER['HTTPS']) !== "off"
    ) {
        return true;
    }
    if (
        isset($_SERVER['SERVER_PORT']) &&
        $_SERVER['SERVER_PORT'] == 443
    ) {
        return true;
    }
    $proxyHeaders = [
        "HTTP_X_FORWARDED_PROTO",
        "HTTP_X_CLIENT_SCHEME",
        "HTTP_FRONT_END_HTTPS",
    ];
    foreach ($proxyHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            $value = strtolower($_SERVER[$header]);
            if (
                $value === "https" ||
                (
                    $header === "HTTP_FRONT_END_HTTPS" &&
                    $value !== "off"
                )) {
                return true;
            }
        }
    }
    if (!empty($_SERVER['HTTP_CF_VISITOR'])) {
        $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
        if (
            isset($cfVisitor['scheme']) &&
            $cfVisitor['scheme'] === "https"
        ) {
            return true;
        }
    }
    return false;
}
/**
 * @description: 获取服务器信息
 * @param {*}
 * @return array
 */
function server_info()
{
    $info = [
        "os"      => php_uname("s"),
        "sys"     => $_SERVER['SERVER_SOFTWARE'],
        "php"     => PHP_VERSION,
        "opcache" => null,
    ];
    if (
        extension_loaded("Zend OPcache") &&
        function_exists('opcache_get_status')
    ) {
        $opcache = opcache_get_status(false);
        if (
            $opcache !== false &&
            isset($opcache['memory_usage'], $opcache['opcache_statistics'])
        ) {
            $info['opcache'] = [
                "used_memory"      => getunit($opcache['memory_usage']['used_memory'], "M"),
                "opcache_hit_rate" => sprintf("%.2f", $opcache['opcache_statistics']['opcache_hit_rate']) . "%",
            ];
        }
    }
    return $info;
}
