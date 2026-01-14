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
        $this->api         = new AliyunApi($this->cfg);
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
            "api" => "https://{$this->cfg['Host']}/{$args['path']}",
            "AccessKeyId" => $this->cfg['AccessKeyId'],
            "Bucket" => $this->cfg['Bucket'],
            "Region" => $this->cfg['Region'],
            "headers" => $sign,
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
        $token = $this->token([
            "method"  => "PUT",
            "path"    => str_replace(PATH_WEB, "", $file),
            "headers" => [
                "Content-Type" => mime_content_type($file),
            ],
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
            "type" => "POST",
            "url"  => "https://{$this->cfg['Host']}/?delete",
            "data"    => $body,
            "headers" => $sign,
        ], $http_info);
        return [
            "code" => $http_info['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
    }
    /**
     * @description: 表单上传
     * @param string $dir 上传目录
     * @return array
     */
    public function policy($dir)
    {
        $expiration = str_replace("+00:00", ".000Z", gmdate("c", time() + 30));
        $conditions = [
            [
                0 => "content-length-range",
                1 => 0,
                2 => 1048576000,
            ], [
                0 => "starts-with",
                1 => "\$key",
                2 => $dir,
            ],
        ];
        $policy = json_encode([
            "expiration" => $expiration,
            "conditions" => $conditions,
        ]);
        $base64_policy  = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature      = base64_encode(hash_hmac("sha1", $string_to_sign, $this->cfg['AccessKeySecret'], true));
        return [
            "api" => "https://{$this->cfg['Host']}/",
            "OSSAccessKeyId" => $this->cfg['AccessKeyId'],
            "policy"         => $base64_policy,
            "signature"      => $signature,
            "dir"            => $dir,
        ];
    }
}
