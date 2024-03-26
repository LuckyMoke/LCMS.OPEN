<?php
class TOTP
{
    public $_codeLength, $_interval;
    public function __construct($codeLength = 6, $interval = 30)
    {
        $this->_codeLength = $codeLength;
        $this->_interval   = $interval;
    }
    /**
     * @description: 生成密钥
     * @param string $key
     * @return string
     */
    public function createSecret($key)
    {
        $chars  = $this->_getBase32LookupTable();
        $secret = randstr(16, "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567");
        $rand   = substr(md5($key), 8, 16);
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[ord($rand[$i]) & 31];
        }
        return $secret;
    }
    /**
     * @description: 获取验证码
     * @param string $secret
     * @param int $interval
     * @return string
     */
    public function getCode($secret)
    {
        $timeSlice = floor(time() / $this->_interval);
        $secretkey = $this->_base32Decode($secret);
        $time      = chr(0) . chr(0) . chr(0) . chr(0) . pack("N*", $timeSlice);
        $hm        = hash_hmac("SHA1", $time, $secretkey, true);
        $offset    = ord(substr($hm, -1)) & 0x0F;
        $hashpart  = substr($hm, $offset, 4);
        $value     = unpack("N", $hashpart);
        $value     = $value[1];
        $value     = $value & 0x7FFFFFFF;
        $modulo    = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, "0", STR_PAD_LEFT);
    }

    /**
     * @description: 获取二维码参数
     * @param string $name
     * @param string} $secret
     * @return string
     */
    public function getQRCode($name, $secret)
    {
        return "otpauth://totp/{$name}?secret={$secret}&algorithm=SHA1&digits={$this->_codeLength}&period={$this->_interval}";
    }
    /**
     * @description: 检测验证码
     * @param string $secret
     * @param string $code
     * @param int $discrepancy
     * @return bool
     */
    public function verifyCode($secret = "", $code = "", $discrepancy = 1)
    {
        if (!$secret || !$code) {
            return false;
        }
        $timeSlice = floor(time() / $this->_interval);
        if (strlen($code) != 6) {
            return false;
        }
        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = $this->getCode($secret, $timeSlice + $i);
            if ($this->timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @description: base32解码
     * @param string $secret
     * @return string
     */
    protected function _base32Decode($secret)
    {
        if (empty($secret)) {
            return "";
        }
        $base32chars        = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount   = substr_count($secret, $base32chars[32]);
        $allowedValues      = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }
        $secret       = str_replace("=", "", $secret);
        $secret       = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = "";
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, "0", STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
            }
        }
        return $binaryString;
    }
    /**
     * @description: 获取base32表
     * @return array
     */
    protected function _getBase32LookupTable()
    {
        return [
            "A", "B", "C", "D", "E", "F", "G", "H",
            "I", "J", "K", "L", "M", "N", "O", "P",
            "Q", "R", "S", "T", "U", "V", "W", "X",
            "Y", "Z", "2", "3", "4", "5", "6", "7",
            "=",
        ];
    }
    /**
     * @description: 时间安全比较
     * @param string $safeString
     * @param string $userString
     * @return bool
     */
    private function timingSafeEquals($safeString, $userString)
    {
        if (function_exists("hash_equals")) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen != $safeLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }
        return $result === 0;
    }
}
