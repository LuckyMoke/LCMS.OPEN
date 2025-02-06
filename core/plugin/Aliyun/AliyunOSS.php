<?php
require "libs/Aliyun.Api.php";
class AliyunOSS
{
    public $cfg = [];
    public $api;
    public function __construct($config)
    {
        $this->cfg           = $config;
        $this->cfg['Region'] = str_replace([
            "http://", "https://", "/", "oss-", ".aliyuncs.com",
        ], "", $this->cfg['Region']);
        $this->cfg = array_merge($this->cfg, [
            "Alg"     => "OSS4-HMAC-SHA256",
            "Service" => "oss",
        ]);
        $this->api = new AliyunApi(array_merge($this->cfg, [
            "Alg"     => "OSS4-HMAC-SHA256",
            "Service" => "oss",
        ]));
        $this->cfg['Host'] = "{$this->cfg['Bucket']}.oss-{$this->cfg['Region']}.aliyuncs.com";
    }
    /**
     * @description: 获取临时token
     * @param {*}
     * @return {*}
     */
    public function token($args = [])
    {
        if ($args['path']) {
            $args['path'] = ltrim($args['path'], "/");
        }
        $sign = $this->api->sign([
            "method"  => $args['method'],
            "headers" => array_merge([
                "Content-Type" => "multipart/form-data",
            ], $args['headers'] ?? []),
            "url"     => "https://{$this->cfg['Host']}/{$this->cfg['Bucket']}/{$args['path']}",
        ]);
        return [
            "api"         => "https://{$this->cfg['Host']}/{$args['path']}",
            "AccessKeyId" => $this->cfg['AccessKeyId'],
            "Bucket"      => $this->cfg['Bucket'],
            "Region"      => $this->cfg['Region'],
            "headers"     => $sign,
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
        $file  = str_replace(PATH_WEB, "", $file);
        $token = $this->token([
            "method" => "PUT",
            "path"   => $file,
        ]);
        $result = HTTP::request([
            "type"    => "PUT",
            "url"     => $token['api'],
            "data"    => $body,
            "headers" => $token['headers'],
        ], $http_info);
        return [
            "code" => $http_info['http_code'] == 200 ? 1 : 0,
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
        $files = implode("", $files);
        $body  = "<Delete><Quiet>true</Quiet>{$files}</Delete>";
        $sign  = $this->api->sign([
            "method"  => "POST",
            "headers" => [
                "Content-Type" => "application/xml",
                "Content-MD5"  => base64_encode(md5($body, true)),
            ],
            "url"     => "https://{$this->cfg['Host']}/{$this->cfg['Bucket']}/?delete",
        ]);
        HTTP::request([
            "type"    => "POST",
            "url"     => "https://{$this->cfg['Host']}/?delete",
            "data"    => $body,
            "headers" => $sign,
        ], $http_info);
        return [
            "code" => $http_info['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
    }
}
