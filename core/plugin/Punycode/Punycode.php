<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-11-28 12:40:15
 * @LastEditTime: 2022-11-28 13:19:00
 * @Description: 域名转换
 * Copyright 2022 运城市盘石网络科技有限公司
 */
namespace PQCMS;

class Punycode
{
    const BASE         = 36;
    const TMIN         = 1;
    const TMAX         = 26;
    const SKEW         = 38;
    const DAMP         = 700;
    const INITIAL_BIAS = 72;
    const INITIAL_N    = 128;
    const PREFIX       = 'xn--';
    const DELIMITER    = '-';
    /**
     * @description: 编码表
     * @return {*}
     */
    protected static $encodeTable = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    );
    /**
     * @description: 解码表
     * @return {*}
     */
    protected static $decodeTable = array(
        'a' => 0, 'b'  => 1, 'c'  => 2, 'd'  => 3, 'e'  => 4, 'f'  => 5,
        'g' => 6, 'h'  => 7, 'i'  => 8, 'j'  => 9, 'k'  => 10, 'l' => 11,
        'm' => 12, 'n' => 13, 'o' => 14, 'p' => 15, 'q' => 16, 'r' => 17,
        's' => 18, 't' => 19, 'u' => 20, 'v' => 21, 'w' => 22, 'x' => 23,
        'y' => 24, 'z' => 25, '0' => 26, '1' => 27, '2' => 28, '3' => 29,
        '4' => 30, '5' => 31, '6' => 32, '7' => 33, '8' => 34, '9' => 35,
    );
    /**
     * @description: 字符编码
     * @return {*}
     */
    protected $encoding;
    public function __construct($encoding = 'UTF-8')
    {
        $this->encoding = $encoding;
    }
    /**
     * @description: 域名编码
     * @param string $input
     * @return string
     */
    public function encode($input)
    {
        $input = mb_strtolower($input, $this->encoding);
        $parts = explode('.', $input);
        foreach ($parts as &$part) {
            $length = strlen($part);
            if ($length < 1) {
                return $input;
            }
            $part = $this->encodePart($part);
        }
        $output = implode('.', $parts);
        $length = strlen($output);
        if ($length > 255) {
            return $input;
        }
        return $output;
    }
    /**
     * @description: 域名部分编码
     * @param string $input
     * @return string
     */
    protected function encodePart($input)
    {
        $codePoints = $this->listCodePoints($input);
        $n          = static::INITIAL_N;
        $bias       = static::INITIAL_BIAS;
        $delta      = 0;
        $h          = $b          = count($codePoints['basic']);
        $output     = '';
        foreach ($codePoints['basic'] as $code) {
            $output .= $this->codePointToChar($code);
        }
        if ($input === $output) {
            return $output;
        }
        if ($b > 0) {
            $output .= static::DELIMITER;
        }
        $codePoints['nonBasic'] = array_unique($codePoints['nonBasic']);
        sort($codePoints['nonBasic']);
        $i      = 0;
        $length = mb_strlen($input, $this->encoding);
        while ($h < $length) {
            $m     = $codePoints['nonBasic'][$i++];
            $delta = $delta + ($m - $n) * ($h + 1);
            $n     = $m;
            foreach ($codePoints['all'] as $c) {
                if ($c < $n || $c < static::INITIAL_N) {
                    $delta++;
                }
                if ($c === $n) {
                    $q = $delta;
                    for ($k = static::BASE;; $k += static::BASE) {
                        $t = $this->calculateThreshold($k, $bias);
                        $q = intval($q);
                        if ($q < $t) {
                            break;
                        }

                        $code = $t + (($q - $t) % (static::BASE - $t));
                        $output .= static::$encodeTable[$code];
                        $q = ($q - $t) / (static::BASE - $t);
                    }

                    $output .= static::$encodeTable[$q];
                    $bias  = $this->adapt($delta, $h + 1, ($h === $b));
                    $delta = 0;
                    $h++;
                }
            }
            $delta++;
            $n++;
        }
        $out    = static::PREFIX . $output;
        $length = strlen($out);
        if ($length > 63 || $length < 1) {
            return $input;
        }
        return $out;
    }
    /**
     * @description: 域名解码
     * @param string $input
     * @return string
     */
    public function decode($input)
    {
        $input = strtolower($input);
        $parts = explode('.', $input);
        foreach ($parts as &$part) {
            $length = strlen($part);
            if ($length > 63 || $length < 1) {
                return $input;
            }
            if (strpos($part, static::PREFIX) !== 0) {
                continue;
            }

            $part = substr($part, strlen(static::PREFIX));
            $part = $this->decodePart($part);
        }
        $output = implode('.', $parts);
        $length = strlen($output);
        if ($length > 255) {
            return $input;
        }
        return $output;
    }
    /**
     * @description: 域名部分解码
     * @param string $input
     * @return string
     */
    protected function decodePart($input)
    {
        $n      = static::INITIAL_N;
        $i      = 0;
        $bias   = static::INITIAL_BIAS;
        $output = '';
        $pos    = strrpos($input, static::DELIMITER);
        if ($pos !== false) {
            $output = substr($input, 0, $pos++);
        } else {
            $pos = 0;
        }
        $outputLength = strlen($output);
        $inputLength  = strlen($input);
        while ($pos < $inputLength) {
            $oldi = $i;
            $w    = 1;
            for ($k = static::BASE;; $k += static::BASE) {
                $digit = static::$decodeTable[$input[$pos++]];
                $i     = $i + ($digit * $w);
                $t     = $this->calculateThreshold($k, $bias);
                if ($digit < $t) {
                    break;
                }
                $w = $w * (static::BASE - $t);
            }
            $bias   = $this->adapt($i - $oldi, ++$outputLength, ($oldi === 0));
            $n      = $n + (int) ($i / $outputLength);
            $i      = $i % ($outputLength);
            $output = mb_substr($output, 0, $i, $this->encoding) . $this->codePointToChar($n) . mb_substr($output, $i, $outputLength - 1, $this->encoding);
            $i++;
        }
        return $output;
    }
    /**
     * @description: 计算 TMIN 和 TMAX 之间的偏置阈值
     * @param integer $k
     * @param integer $bias
     * @return integer
     */
    protected function calculateThreshold($k, $bias)
    {
        if ($k <= $bias+static::TMIN) {
            return static::TMIN;
        } elseif ($k >= $bias+static::TMAX) {
            return static::TMAX;
        }
        return $k - $bias;
    }
    /**
     * @description: 偏差适应
     * @param integer $delta
     * @param integer $numPoints
     * @param boolean $firstTime
     * @return integer
     */
    protected function adapt($delta, $numPoints, $firstTime)
    {
        $delta = (int) (
            ($firstTime)
            ? $delta / static::DAMP
            : $delta / 2
        );
        $delta += (int) ($delta / $numPoints);
        $k = 0;
        while ($delta > ((static::BASE-static::TMIN) * static::TMAX) / 2) {
            $delta = (int) ($delta / (static::BASE-static::TMIN));
            $k     = $k+static::BASE;
        }
        $k = $k + (int) (((static::BASE-static::TMIN + 1) * $delta) / ($delta+static::SKEW));
        return $k;
    }
    /**
     * @description: 列出给定输入的代码点
     * @param string $input
     * @return array
     */
    protected function listCodePoints($input)
    {
        $codePoints = array(
            'all'      => array(),
            'basic'    => array(),
            'nonBasic' => array(),
        );
        $length = mb_strlen($input, $this->encoding);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($input, $i, 1, $this->encoding);
            $code = $this->charToCodePoint($char);
            if ($code < 128) {
                $codePoints['all'][] = $codePoints['basic'][] = $code;
            } else {
                $codePoints['all'][] = $codePoints['nonBasic'][] = $code;
            }
        }
        return $codePoints;
    }
    /**
     * @description: 将单字节或多字节字符转换为其代码点
     * @param string $char
     * @return integer
     */
    protected function charToCodePoint($char)
    {
        $code = ord($char[0]);
        if ($code < 128) {
            return $code;
        } elseif ($code < 224) {
            return (($code - 192) * 64) + (ord($char[1]) - 128);
        } elseif ($code < 240) {
            return (($code - 224) * 4096) + ((ord($char[1]) - 128) * 64) + (ord($char[2]) - 128);
        } else {
            return (($code - 240) * 262144) + ((ord($char[1]) - 128) * 4096) + ((ord($char[2]) - 128) * 64) + (ord($char[3]) - 128);
        }
    }
    /**
     * @description: 将代码点转换为其单字节或多字节字符
     * @param integer $code
     * @return string
     */
    protected function codePointToChar($code)
    {
        if ($code <= 0x7F) {
            return chr($code);
        } elseif ($code <= 0x7FF) {
            return chr(($code >> 6) + 192) . chr(($code&63) + 128);
        } elseif ($code <= 0xFFFF) {
            return chr(($code >> 12) + 224) . chr((($code >> 6)&63) + 128) . chr(($code&63) + 128);
        } else {
            return chr(($code >> 18) + 240) . chr((($code >> 12)&63) + 128) . chr((($code >> 6)&63) + 128) . chr(($code&63) + 128);
        }
    }
}
