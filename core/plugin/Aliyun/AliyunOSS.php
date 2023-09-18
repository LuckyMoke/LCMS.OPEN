<?php
class AliyunOSS
{
    public $cfg = [];
    public $api = "";
    public function __construct($config)
    {
        $this->cfg = $config;
        //Region清理
        $this->cfg['Region'] = str_replace([
            "http://", "https://", "/", "oss-", ".aliyuncs.com",
        ], "", $this->cfg['Region']);
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
            "expiration" => gmdate("Y-m-d\TH:i:s\Z", time() + 3600),
            "conditions" => [["content-length-range", 0, 1073741824]],
        ]));
        $sign = base64_encode(hash_hmac("sha1", $policy, $this->cfg['AccessKeySecret'], true));
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
        $body    = file_get_contents($file);
        $file    = str_replace(PATH_WEB, "", $file);
        $path    = "/" . $this->cfg['Bucket'] . "/{$file}";
        $headers = [
            "PUT"          => "/{$file}",
            "Content-Md5"  => base64_encode(md5($body, true)),
            "Content-Type" => mime_content_type($file),
            "Date"         => gmdate('D, d M Y H:i:s T'),
            "Host"         => $this->api,
        ];
        $url    = "https://" . $this->api . "/{$file}";
        $result = HTTP::put($url, $body, array_merge($headers, [
            "Authorization" => "OSS " . $this->cfg['AccessKeyId'] . ":" . $this->sign("PUT", $path, $headers),
        ]));
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => $result ? "SUCCESS" : "上传失败",
        ];
    }
    /**
     * @description: 删除指定文件
     * @param array|string $files
     * @return array
     */
    public function delete($files)
    {
        $files = is_array($files) ? $files : [$files];
        foreach ($files as $index => $file) {
            $files[$index] = "<Object><Key>{$file}</Key></Object>";
        }
        $files   = implode("", $files);
        $body    = "<Delete><Quiet>true</Quiet>{$files}</Delete>";
        $headers = [
            "POST"         => "/?delete",
            "Host"         => $this->api,
            "Date"         => gmdate('D, d M Y H:i:s T'),
            "Content-Type" => "application/xml",
            "Content-MD5"  => base64_encode(md5($body, true)),
        ];
        $url = "https://" . $this->api . "/?delete";
        HTTP::post($url, $body, false, array_merge($headers, [
            "Authorization" => "OSS " . $this->cfg['AccessKeyId'] . ":" . $this->sign("POST", "/" . $this->cfg['Bucket'] . "/?delete", $headers),
        ]));
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
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
}
