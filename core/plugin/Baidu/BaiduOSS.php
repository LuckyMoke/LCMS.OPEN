<?php
require "libs/Baidu.Api.php";
class BaiduOSS
{
    public $cfg = [];
    public $Api;
    public $host;
    public function __construct($config)
    {
        $this->cfg = $config;
        $this->Api = new BaiduApi([
            "AccessKey" => $this->cfg['AccessKey'],
            "SecretKey" => $this->cfg['SecretKey'],
        ]);
        //Region清理
        $this->cfg['Region'] = str_replace([
            "http://", "https://", "/",
        ], "", $this->cfg['Region']);
        //拼接接口地址
        $this->host = $this->cfg['Region'];
        //截取区域参数
        $this->cfg['Region'] = str_replace("{$this->cfg['Bucket']}.", "", $this->cfg['Region']);
        $this->cfg['Region'] = explode(".", $this->cfg['Region'])[0];
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
        $sign = hash_hmac("sha256", $policy, $this->cfg['SecretKey']);
        return [
            "api"       => "https://{$this->host}",
            "AccessKey" => $this->cfg['AccessKey'],
            "policy"    => $policy,
            "signature" => $sign,
            "Bucket"    => $this->cfg['Bucket'],
            "Region"    => $this->cfg['Region'],
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
        $body   = file_get_contents($file);
        $sign   = $this->Api->sign("PUT", "https://{$this->host}/" . str_replace(PATH_WEB, "", $file));
        $result = HTTP::request([
            "type"    => "PUT",
            "url"     => $sign['api'],
            "data"    => $body,
            "headers" => [
                "Host"           => $this->host,
                "Content-Type"   => mime_content_type($file),
                "Content-Length" => filesize($file),
                "Authorization"  => $sign['sign'],
                "x-bce-date"     => $sign['date'],
            ],
        ], $http_info);
        return [
            "code" => $http_info['http_code'] == 200 ? 1 : 0,
            "msg"  => $result ? "上传失败" : "SUCCESS",
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
            $files[$index] = [
                "key" => $file,
            ];
        }
        $sign = $this->Api->sign("POST", "https://{$this->host}/?delete=");
        HTTP::request([
            "type"    => "POST",
            "url"     => $sign['api'],
            "data"    => json_encode([
                "objects" => $files,
            ]),
            "headers" => [
                "Host"          => $this->host,
                "Content-Type"  => "application/json",
                "Authorization" => $sign['sign'],
                "x-bce-date"    => $sign['date'],
            ],
        ], $http_info);
        return [
            "code" => $http_info['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
    }
}
