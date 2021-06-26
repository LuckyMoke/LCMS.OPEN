<?php
class AliyunOSS
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
        // 截取区域参数
        $this->cfg['Region'] = str_replace(["oss-", ".aliyuncs.com"], "", $this->cfg['Region']);
        // 拼接接口地址
        $this->api = "{$this->cfg['Bucket']}.oss-{$this->cfg['Region']}.aliyuncs.com";
    }
    /**
     * @description: 获取临时token
     * @param {*}
     * @return {*}
     */
    public function token()
    {
        $policy = base64_encode(json_encode([
            "expiration" => gmdate("Y-m-d\TH:m:s\Z", time() + 3600),
            "conditions" => [["content-length-range", 0, 1048576000]],
        ]));
        $sign = base64_encode(hash_hmac('sha1', $policy, $this->cfg['AccessKeySecret'], true));
        return [
            "api"         => "https://" . $this->api,
            "AccessKeyId" => $this->cfg['AccessKeyId'],
            "policy"      => $policy,
            "signature"   => $sign,
            "Bucket"      => $this->cfg['Bucket'],
            "Region"      => $this->cfg['Region'],
        ];
    }
    /**
     * @description: 上传文件
     * @param string $file
     * @return {*}
     */
    public function upload($file)
    {
        $file = path_absolute($file);
        if (!is_file($file)) {
            return [
                "code" => 0, "msg" => "未找到文件",
            ];
        }
        $body  = file_get_contents($file);
        $finfo = finfo_open(FILEINFO_MIME);
        $mime  = finfo_file($finfo, $file);
        finfo_close($finfo);
        $file    = str_replace(PATH_WEB, "", $file);
        $path    = "/" . $this->cfg['Bucket'] . "/{$file}";
        $headers = [
            "PUT"          => "/{$file} HTTP/1.1",
            "Content-Md5"  => base64_encode(md5($body, true)),
            "Content-Type" => explode(";", $mime)[0],
            "Date"         => gmdate('D, d M Y H:i:s T'),
            "Host"         => $this->api,
        ];
        $headers['Authorization'] = "OSS " . $this->cfg['AccessKeyId'] . ":" . $this->sign("PUT", $path, $headers);
        if (!$this->request("PUT", "https://" . $this->api . "/{$file}", $body, $headers)) {
            return [
                "code" => 1,
                "msg"  => "success",
            ];
        }
    }
    /**
     * @description: 删除文件
     * @param string $file
     * @return {*}
     */
    public function delete($file)
    {
        $path    = "/" . $this->cfg['Bucket'] . "/{$file}";
        $headers = [
            "DELETE"       => "/{$file} HTTP/1.1",
            "Content-Md5"  => "",
            "Content-Type" => "application/octet-stream",
            "Date"         => gmdate('D, d M Y H:i:s T'),
            "Host"         => $this->api,
        ];
        $headers['Authorization'] = "OSS " . $this->cfg['AccessKeyId'] . ":" . $this->sign("DELETE", $path, $headers);
        $this->request("DELETE", "https://" . $this->api . "/{$file}", "", $headers);
    }
    /**
     * @description: 签名
     * @param string $method
     * @param string $path
     * @param array $headers
     * @return string
     */
    public function sign($method, $path, $headers = [])
    {
        if ($headers) {
            ksort($headers);
            $head = "";
            foreach ($headers as $key => $val) {
                if (
                    strtolower($key) === 'content-md5' ||
                    strtolower($key) === 'content-type' ||
                    strtolower($key) === 'date'
                ) {
                    $head .= $val . "\n";
                } elseif (substr(strtolower($key), 0, 6) === "x-oss-") {
                    $head .= strtolower($head) . ":{$val}\n";
                }
            }
        }
        $sign = "{$method}\n{$head}{$path}";
        return base64_encode(hash_hmac('sha1', $sign, $this->cfg['AccessKeySecret'], true));

    }
    private function request($method = "POST", $url, $data = "", $headers = [])
    {
        $ch = curl_init($url);
        if ($headers) {
            $header[] = "{$method} {$headers[$method]}";
            unset($headers[$method]);
            foreach ($headers as $key => $val) {
                $header[] = "{$key}: {$val}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
