<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2024-07-14 14:15:24
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
        echo htmlspecialchars(print_r($vars, true), ENT_COMPAT, "ISO-8859-1");
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
    $date = in_string($date, ["-", ":"]) ? strtotime($date) : $date;
    switch ($type) {
        case 'date':
            $type = "Y-m-d";
            break;
        case 'time':
            $type = "H:i:s";
            break;
        case 'y':
            $type = "Y";
            break;
        case 'h':
            $type = "H";
            break;
        case 'datetime':
        case '':
            $type = "Y-m-d H:i:s";
            break;
    }
    return date($type, $date);
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
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz";
            break;
        case 'all':
            $str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz23456789";
            break;
        default:
            $str = $type;
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
    if (!$unit) {
        if ($size >= 1073741824) {
            $unit = "GB";
        } elseif ($size >= 1048576) {
            $unit = "MB";
        } elseif ($size >= 1024) {
            $unit = "KB";
        } else {
            $unit = "B";
        }
    }
    switch ($unit) {
        case 'GB':
            $size = $size / 1073741824;
            $size = sprintf("%.2f", $size);
            break;
        case 'MB':
            $size = $size / 1048576;
            $size = sprintf("%.2f", $size);
            break;
        case 'B':
            $size = sprintf("%.2f", $size);
            break;
        default:
            $size = $size / 1024;
            $size = sprintf("%.2f", $size);
            break;
    }
    return $size . $unit;
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
function goheader($url)
{
    header("Location: {$url}");
    exit;
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function gbk2utf8($str = "")
{
    if (mb_detect_encoding($str, [
        "ASCII", "GB2312", "GBK", "BIG5", "UTF-8",
    ]) != "UTF-8") {
        return mb_convert_encoding($str, "UTF-8", "GBK, GB2312, BIG5, ASCII");
    } else {
        return $str;
    }
}
/**
 * @description: 编码转换
 * @param string $str
 * @return string
 */
function utf82gbk($str = "")
{
    if (mb_detect_encoding($str, [
        "ASCII", "GB2312", "GBK", "BIG5", "UTF-8",
    ]) == "UTF-8") {
        return mb_convert_encoding($str, "GBK", "UTF-8");
    } else {
        return $str;
    }
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
        $filters = [
            "\\"        => "&#92;",
            "*"         => "&#42;",
            "%5C"       => "&#37;&#53;&#67;",
            "%22"       => "&#37;&#50;&#50;",
            "%27"       => "&#37;&#50;&#55;",
            "create"    => "&#99;&#114;&#101;&#97;&#116;&#101;",
            "select"    => "&#115;&#101;&#108;&#101;&#99;&#116;",
            "insert"    => "&#105;&#110;&#115;&#101;&#114;&#116;",
            "update"    => "&#117;&#112;&#100;&#97;&#116;&#101;",
            "delete"    => "&#100;&#101;&#108;&#101;&#116;&#101;",
            "union"     => "&#117;&#110;&#105;&#111;&#110;",
            "into"      => "&#105;&#110;&#116;&#111;",
            "alter"     => "&#97;&#108;&#116;&#101;&#114;",
            "truncate"  => "&#116;&#114;&#117;&#110;&#99;&#97;&#116;&#101;",
            "drop"      => "&#100;&#114;&#111;&#112;",
            "load_file" => "&#108;&#111;&#97;&#100;&#95;&#102;&#105;&#108;&#101;",
            "outfile"   => "&#111;&#117;&#116;&#102;&#105;&#108;&#101;",
            "sleep"     => "&#115;&#108;&#101;&#101;&#112;",
            "/script"   => "&#47;&#115;&#99;&#114;&#105;&#112;&#116;",
            "script"    => "&#115;&#99;&#114;&#105;&#112;&#116;",
            "eval"      => "&#101;&#118;&#97;&#108;",
            "document"  => "&#100;&#111;&#99;&#117;&#109;&#101;&#110;&#116;",
        ];
        if (!is_url($string)) {
            $string = htmlspecialchars($string);
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
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = filterform($val);
        }
    } else {
        if (L_MODULE != "admin") {
            $string = sqlinsert($string);
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
 * @description: 获取转义后的域名
 * @param string $host
 * @return string
 */
function realhost($host = "")
{
    $cache = $host;
    if (in_string($host, "://")) {
        $domain = parse_url($host);
        $cache  = $domain['host'];
    }
    if ($cache) {
        require_once PATH_CORE_PLUGIN . "Punycode/Punycode.php";
        $Punycode = new PQCMS\Punycode();
        $cache    = $Punycode->encode($cache);
    }
    return $domain ? str_replace($domain['host'], $cache, $host) : $cache;
}
/**
 * @description: 获取根域名
 * @param {*} $domain
 * @return {*}
 */
function roothost($domain = "")
{
    if (filter_var($domain, FILTER_VALIDATE_IP)) {
        // 如果是IP地址，直接输入IP
        $host = $domain;
    } elseif ($domain) {
        switch ($domain) {
            case 'localhost':
                $host = $domain;
                break;
            default:
                if (in_string($domain, "://")) {
                    $domain = parse_url($domain);
                    $domain = $domain['host'];
                }
                $match = explode(".", $domain);
                $match = array_slice($match, -3);
                $match = array_reverse($match);
                switch ($match[0]) {
                    case 'cn':
                        if (!in_array($match[1], [
                            "com", "net", "org", "gov", "edu", "ac", "bj", "sh", "tj", "cq", "he", "sn", "sx", "nm", "ln", "jl", "hl", "js", "zj", "ah", "fj", "jx", "sd", "ha", "hb", "hn", "gd", "gx", "hi", "sc", "gz", "yn", "gs", "qh", "nx", "xj", "tw", "hk", "mo", "xz",
                        ])) {
                            unset($match[2]);
                        }
                        break;
                    default:
                        if (!in_array($match[1], [
                            "com", "net", "org", "gov", "edu", "ac",
                        ])) {
                            unset($match[2]);
                        }
                        break;
                }
                $match = array_reverse($match);
                $host  = implode(".", $match);
                $host  = explode(":", $host);
                $host  = $host[0];
                break;
        }
    }
    return $host ? realhost($host) : "";
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
    $res = openssl_pkey_new([
        "private_key_bits" => $bit,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);
    if ($res === false) {
        return false;
    } else {
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];
        return [
            "pubKey"  => $pubKey,
            "privKey" => $privKey,
        ];
    }
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
    if (strlen($phone) == 11 && preg_match("/^1[3-9]\d{9}$/", $phone)) {
        $flag = true;
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
 * @description: 判断是否为IP地址
 * @param string $ip
 * @param string $type [ipv4、ipv6]
 * @return bool
 */
function is_ip($ip = "", $type = "")
{
    if (!$ip || $ip == "unknown") {
        return false;
    }
    switch ($type) {
        case 'ipv6':
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return true;
            }
            break;
        case 'ipv4':
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return true;
            }
            break;
        default:
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return true;
            }
            break;
    }
    return false;
}
/**
 * @description: 判断是否内网IP
 * @param string $ip
 * @return bool
 */
function is_intranet_ip($ip = "")
{
    if (is_ip($ip, "ipv4")) {
        $ipnum = ip2long($ip);
        if ($ipnum == 0) {
            return false;
        }
        if (
            ($ipnum >= 2130706433 && $ipnum <= 2130706687) ||
            ($ipnum >= 3232235520 && $ipnum <= 3232301055) ||
            ($ipnum >= 2886729728 && $ipnum <= 2887778303) ||
            ($ipnum >= 167772161 && $ipnum <= 184549375)
        ) {
            return true;
        }
    } elseif (is_ip($ip, "ipv6")) {
        if (
            strpos($ip, "fc") === 0 ||
            strpos($ip, "fd") === 0 ||
            strpos($ip, "fe80") === 0
        ) {
            return true;
        }
    }
    return false;
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
 * @description: url安全的base64_ecnode
 * @param string $str
 * @return string
 */
function urlsafe_base64_encode($str)
{
    $data = base64_encode($str);
    return str_replace([
        "+", "/", "=",
    ], [
        "-", "_", "",
    ], $data);
}
/**
 * @description: url安全的base64_decode
 * @param string $str
 * @return string
 */
function urlsafe_base64_decode($str)
{
    $data = str_replace([
        "-", "_",
    ], [
        "+", "/",
    ], $str);
    return base64_decode($data);
}
/**
 * @description: HTML内容解码懒加载
 * @param string $body
 * @param bool $lazyload
 * @param bool $watermark
 * @return string
 */
function html_editor($body = "", $lazyload = false)
{
    if ($body) {
        $body = is_base64($body) ? base64_decode($body) : $body;
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/is', $body, $match);
        if ($match[1]) {
            $tags = [];
            foreach ($match[1] as $index => $src) {
                $tag = $match[0][$index];
                $sch = [$src];
                $rce = [oss($src)];
                if ($lazyload) {
                    if (in_string($tag, "class=")) {
                        $sch = array_merge($sch, [
                            "class='",
                            'class="',
                        ]);
                        $rce = array_merge($rce, [
                            "class='lazyload ",
                            'class="lazyload ',
                        ]);
                    } else {
                        $sch = array_merge($sch, ["<img "]);
                        $rce = array_merge($rce, ['<img class="lazyload" ']);
                    }
                    $sch = array_merge($sch, ["src="]);
                    $rce = array_merge($rce, ["data-src="]);
                }
                $tags[] = str_replace($sch, $rce, $tag);
            }
            $body = str_replace($match[0], $tags, $body);
        }
        if ($lazyload) {
            $body = preg_replace('/(<video[^>]*)src="(.*?)"(.*?)<\/video>/', '<video class="video-js" data-src="$2"$3></video>', $body);
            $body = preg_replace('/(<iframe[^>]*)src="(.*?)"(.*?)><\/iframe>/', '<iframe frameborder="no" border="0" data-src="$2"$3></iframe>', $body);
        }
        $body = str_replace([
            "<p><br /></p>",
            "<p><br/></p>",
            "<p><br></p>",
        ], "", $body);
    }
    return $body ?: "";
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
        $webp   = $config['attwebp'] > 0 ? true : false;
        $cfgwat = array_merge($cfgwat, [
            "on"   => $cfgwat['on'] > 0 && $watermark && !in_string($url, ".gif") ? true : false,
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
                    $url .= "?imageMogr2/interlace/1/quality/75";
                    $url .= $webp ? "/format/webp" : "";
                    $url .= $cfgwat['on'] ? "|watermark/2/text/{$cfgwat['text']}/font/V2VuUXVhbllpIE1pY3JvIEhlaQ/fontsize/" . ($cfgwat['size'] * 20) . "/fill" . "/" . urlsafe_base64_encode($cfgwat['fill']) . "/dissolve/{$cfgwat['dissolve']}/gravity/{$cfgwat['gravity']}/dx/{$cfgwat['dx']}/dy/{$cfgwat['dy']}" : "";
                    break;
                case 'tencent':
                    $url .= "?imageMogr2/interlace/1/quality/75";
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
                    $url .= "?x-oss-process=image/auto-orient,1/interlace,1/quality,q_75";
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
                    $url .= "?x-bce-process=image/auto-orient,o_1/interlace,i_progressive/quality,q_75";
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
    if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 1 || $_SERVER['HTTPS'] == "on")) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
        return true;
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
    $serverinfo['os']  = php_uname('s');
    $serverinfo['sys'] = $_SERVER["SERVER_SOFTWARE"];
    $serverinfo['php'] = PHP_VERSION;
    if (function_exists("opcache_get_status")) {
        $opcache = opcache_get_status();
        if ($opcache['memory_usage']) {
            $serverinfo['opcache'] = "内存用量/" . sprintf("%.2f", $opcache['memory_usage']['used_memory'] / 1048576) . "M 缓存命中率/" . sprintf("%.2f", $opcache['opcache_statistics']['opcache_hit_rate']) . "%";
        }
    }
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
