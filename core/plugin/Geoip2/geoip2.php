<?php
use GeoIp2\Database\Reader;

class Geoip2
{
    private $path = PATH_CORE_PLUGIN . "Geoip2/";
    public function __construct()
    {
    }
    /**
     * @description: 判断ip查询功能是否可用
     * @return bool
     */
    public function isready($dbtype = "geoip2")
    {
        if (version_compare(PHP_VERSION, "8.1", "lt")) {
            return false;
        }
        switch ($dbtype) {
            case 'cz88':
                if (
                    is_file("{$this->path}ipdb/cz88_public_v4.czdb") &&
                    is_file("{$this->path}ipdb/cz88_public_v6.czdb") &&
                    is_file("{$this->path}ipdb/cz88.crt")
                ) {
                    return true;
                }
                break;
            case 'geoip2-country':
                if (is_file("{$this->path}ipdb/GeoLite2-Country.mmdb")) {
                    return true;
                }
                break;
            default:
                if (is_file("{$this->path}ipdb/GeoLite2-City.mmdb")) {
                    return true;
                } elseif (is_file("{$this->path}ipdb/GeoLite2-Country.mmdb")) {
                    return true;
                }
                break;
        }
        return false;
    }
    /**
     * @description: 获取IP信息
     * @param string $ip
     * @return array
     */
    public function query($ip, $dbtype = "geoip2")
    {
        //获取IP类型
        $iptype = $this->getIPType($ip);
        if ($iptype) {
            //检测是否内网IP
            if (is_intranet_ip($ip)) {
                return [
                    "ip"       => $ip,
                    "intranet" => true,
                    "type"     => $iptype,
                    "address"  => "内网IP",
                ];
            }
            switch ($dbtype) {
                case 'cz88':
                    return $this->cz88($iptype, $ip);
                    break;
                case 'geoip2-country':
                    return $this->geoip2($iptype, $ip, "country");
                    break;
                default:
                    return $this->geoip2($iptype, $ip);
                    break;
            }
        }
        return false;
    }
    /**
     * @description: 获取IP类型
     * @param string $ip
     * @return string|bool
     */
    private function getIPType($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return "ipv4";
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return "ipv6";
        }
        return false;
    }
    /**
     * @description: Geoip2数据库查询
     * @param string $iptype
     * @param string $ip
     * @return bool|array
     */
    private function geoip2($iptype, $ip, $dbtype = "city")
    {
        require_once "{$this->path}libs/geoip2.phar";
        switch ($dbtype) {
            case 'country':
                if (is_file("{$this->path}ipdb/GeoLite2-Country.mmdb")) {
                    $record = new Reader("{$this->path}ipdb/GeoLite2-Country.mmdb");
                    $func   = "country";
                } else {
                    return false;
                }
                break;
            default:
                if (is_file("{$this->path}ipdb/GeoLite2-City.mmdb")) {
                    $record = new Reader("{$this->path}ipdb/GeoLite2-City.mmdb");
                    $func   = "city";
                } else {
                    return $this->geoip2($iptype, $ip, "country");
                }
                break;
        }
        try {
            $record = $record->$func($ip);
            if ($record->continent->names['zh-CN']) {
                $continent      = $record->continent->names['zh-CN'];
                $continent_code = $record->continent->code;
            }
            if ($record->country->names['zh-CN']) {
                $country      = $record->country->names['zh-CN'];
                $country_code = $record->country->isoCode;
            } elseif ($record->registeredCountry->names['zh-CN']) {
                $country      = $record->registeredCountry->names['zh-CN'];
                $country_code = $record->registeredCountry->isoCode;
            }
            if ($dbtype == "city") {
                if ($record->mostSpecificSubdivision->names['zh-CN']) {
                    $province      = $record->mostSpecificSubdivision->names['zh-CN'];
                    $province_code = $record->mostSpecificSubdivision->isoCode;
                } elseif ($record->subdivisions[0]->names['zh-CN']) {
                    $province      = $record->subdivisions[0]->names['zh-CN'];
                    $province_code = $record->subdivisions[0]->isoCode;
                } elseif ($record->mostSpecificSubdivision->names['en']) {
                    $province      = $record->mostSpecificSubdivision->names['en'];
                    $province_code = $record->mostSpecificSubdivision->isoCode;
                }
                if ($record->city->names['zh-CN']) {
                    $city = $record->city->names['zh-CN'];
                } elseif ($record->city->names['en']) {
                    $city = $record->city->names['en'];
                }
            }
            switch ($country) {
                case '香港':
                case '澳门':
                case '台湾':
                    $country = "中国";
                    break;
            }
            $province = str_replace(["省", "市"], "", $province);
            $city     = str_replace(["市", "县"], "", $city);
            if ($dbtype == "city") {
                $address = $city ?: $province;
                if (!preg_match("/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u", $city)) {
                    $address = $province;
                }
                $address = $country == $address ? $country : "{$country}{$address}";
            } else {
                $address = $country;
            }
            return [
                "ip"             => $ip,
                "intranet"       => false,
                "type"           => $iptype,
                "continent"      => $continent,
                "continent_code" => $continent_code,
                "country"        => $country,
                "country_code"   => $country_code,
                "province"       => $province,
                "province_code"  => $province_code,
                "city"           => $city,
                "address"        => $address,
                "original"       => $record,
            ];
        } catch (\Throwable $th) {
            return false;
        }
    }
    /**
     * @description: 纯真数据库查询
     * @param string $iptype
     * @param string $ip
     * @return bool|array
     */
    private function cz88($iptype, $ip)
    {
        switch ($iptype) {
            case 'ipv4':
                $db = "{$this->path}ipdb/cz88_public_v4.czdb";
                break;
            case 'ipv6':
                $db = "{$this->path}ipdb/cz88_public_v6.czdb";
                break;
        }
        $key = "{$this->path}ipdb/cz88.crt";
        if (!is_file($db) || !is_file($key)) {
            return false;
        }
        require_once "{$this->path}libs/czdb.phar";
        $czdb = new \Czdb\DbSearcher($db, "BTREE", file_get_contents($key));
        try {
            $record   = $czdb->search($ip);
            $region   = explode("	", $record);
            $array    = explode("–", $region[0]);
            $array[1] = str_replace(["省", "市"], "", $array[1]);
            $array[2] = str_replace(["市", "县"], "", $array[2]);
            $array[3] = str_replace("市", "", $array[3]);
            if ($array[2]) {
                if ($array[2] == $array[3]) {
                    $addrsss = "{$array[0]}{$array[2]}";
                } else {
                    $addrsss = "{$array[0]}{$array[2]}{$array[3]}";
                }
            } else {
                $addrsss = "{$array[0]}{$array[1]}";
            }
            $czdb->close();
            return $region[0] ? [
                "ip"        => $ip,
                "intranet"  => false,
                "type"      => $iptype,
                "country"   => $array[0],
                "province"  => $array[1],
                "city"      => $array[2],
                "districts" => $array[3],
                "address"   => $addrsss,
                "isp"       => $region[1],
                "original"  => $record,
            ] : false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
