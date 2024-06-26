<?php
use GeoIp2\Database\Reader;
use ip2region\XdbSearcher;

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
                if (is_file("{$this->path}ipdb/cz88.xdb")) {
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
                    if ($iptype == "ipv4") {
                        return $this->cz88($iptype, $ip);
                    }
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
                    $region      = $record->mostSpecificSubdivision->names['zh-CN'];
                    $region_code = $record->mostSpecificSubdivision->isoCode;
                } elseif ($record->subdivisions[0]->names['zh-CN']) {
                    $region      = $record->subdivisions[0]->names['zh-CN'];
                    $region_code = $record->subdivisions[0]->isoCode;
                }
                if ($record->city->names['zh-CN']) {
                    $city = $record->city->names['zh-CN'];
                }
            }
            switch ($country) {
                case '香港':
                case '澳门':
                case '台湾':
                    $country = "中国";
                    break;
            }
            if ($dbtype == "city") {
                $address = $city ?: $region;
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
                "region"         => $region,
                "region_code"    => $region_code,
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
        $xdb = "{$this->path}ipdb/cz88.xdb";
        if (!is_file($xdb)) {
            return false;
        }
        require_once "{$this->path}libs/XdbSearcher.php";
        try {
            $record = XdbSearcher::newWithFileOnly($xdb)->search($ip);
            $region = explode("	", $record);
            return $region[0] ? [
                "ip"       => $ip,
                "intranet" => false,
                "type"     => $iptype,
                "address"  => $region[0],
                "original" => $record,
            ] : false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
