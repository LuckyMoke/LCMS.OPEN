<?php
class TencentOSS
{
    public $cfg = [];
    public $api = "";
    public function __construct($config)
    {
        $this->cfg = $config;
        //Region清理
        $this->cfg['Region'] = str_replace([
            "http://", "https://", "/", "{$this->cfg['Bucket']}.cos.", ".myqcloud.com",
        ], "", $this->cfg['Region']);
        // 拼接接口地址
        $this->api = "{$this->cfg['Bucket']}.cos.{$this->cfg['Region']}.myqcloud.com";
    }
    /**
     * @description: 获取临时token
     * @param {*}
     * @return {*}
     */
    public function token()
    {
        return $this->getTempKeys([
            'url'             => 'https://sts.tencentcloudapi.com/',
            'domain'          => 'sts.tencentcloudapi.com',
            'proxy'           => '',
            'secretId'        => $this->cfg['SecretId'],
            'secretKey'       => $this->cfg['SecretKey'],
            'bucket'          => $this->cfg['Bucket'],
            'region'          => $this->cfg['Region'],
            'durationSeconds' => 3600,
            'allowPrefix'     => '/*',
            'allowActions'    => [
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload',
            ],

        ]);
    }
    /**
     * @description: 上传文件
     * @param string $file
     * @return {*}
     */
    public function upload($file)
    {
        $name = str_replace("../", "", $file);
        $file = path_absolute($file);
        if (!is_file($file)) {
            return [
                "code" => 0, "msg" => "未找到文件",
            ];
        }
        $body    = file_get_contents($file);
        $headers = [
            "PUT"          => "/{$name}",
            "Host"         => $this->api,
            "Content-Type" => mime_content_type($file),
        ];
        $url    = "https://" . $this->api . "/{$name}";
        $result = HTTP::put($url, $body, array_merge($headers, [
            "Authorization" => $this->sign("put", $name, [], $headers)
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
            "Content-Type" => "application/xml",
            "Content-MD5"  => base64_encode(md5($body, true)),
        ];
        $url = "https://" . $this->api . "/?delete";
        HTTP::post($url, $body, false, array_merge($headers, [
            "Authorization" => $this->sign("post", "", [], $headers)
        ]));
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
    }
    public function sign($method, $path, $params, $headers)
    {
        $KeyTime        = time() . ";" . time() + 3600;
        $SignKey        = hash_hmac("sha1", $KeyTime, $this->cfg['SecretKey']);
        $UrlParamList   = $this->arr2key($params);
        $HttpParameters = $this->arr2str($params);
        $HeaderList     = $this->arr2key($headers);
        $HttpHeaders    = $this->arr2str($headers);
        $HttpString     = "{$method}\n/{$path}\n{$HttpParameters}\n{$HttpHeaders}\n";
        $HttpString     = sha1($HttpString);
        $StringToSign   = "sha1\n{$KeyTime}\n{$HttpString}\n";
        $Signature      = hash_hmac("sha1", $StringToSign, $SignKey);
        return "q-sign-algorithm=sha1&q-ak={$this->cfg['SecretId']}&q-sign-time={$KeyTime}&q-key-time={$KeyTime}&q-header-list={$HeaderList}&q-url-param-list={$UrlParamList}&q-signature={$Signature}";
    }
    private function getTempKeys($config)
    {
        $AppId  = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
        $policy = str_replace('\\/', '/', json_encode([
            'version'   => '2.0',
            'statement' => [
                [
                    'action'    => $config['allowActions'],
                    'effect'    => "allow",
                    'principal' => ["qcs" => ["*"]],
                    'resource'  => [
                        "qcs::cos:{$config['region']}:uid/{$AppId}:{$config['bucket']}{$config['allowPrefix']}",
                    ],
                ],
            ],
        ]));
        $params = [
            'SecretId'        => $config['secretId'],
            'Timestamp'       => time(),
            'Nonce'           => rand(10000, 20000),
            'Action'          => "GetFederationToken",
            'DurationSeconds' => $config['durationSeconds'],
            'Version'         => '2018-08-13',
            'Name'            => 'cos',
            'Region'          => $config['region'],
            'Policy'          => urlencode($policy),
        ];
        $params['Signature'] = $this->getSignature($params, $config['secretKey'], "POST", $config);

        $result = HTTP::post($config['url'], $this->json2str($params));
        $result = $result ? json_decode($result, true) : "";
        if ($result) {
            if ($result['Response']['Error']) {
                return [
                    "code" => 0,
                    "msg"  => $result['Response']['Error']['Message'],
                ];
            } else {
                return $result['Response'];
            }

        }
    }
    private function _hex2bin($data)
    {
        return pack("H" . strlen($data), $data);
    }
    private function json2str($obj, $Not = false)
    {
        ksort($obj);
        $arr = [];
        if (is_array($obj)) {
            foreach ($obj as $key => $val) {
                array_push($arr, $key . '=' . ($Not ? $val : rawurlencode($val)));
            }
            return join('&', $arr);
        }
    }
    private function arr2str($arr)
    {
        if (is_array($arr)) {
            ksort($arr);
            $data = [];
            foreach ($arr as $key => $val) {
                $data[] = strtolower(rawurlencode($key)) . "=" . rawurlencode($val);
            }
            return implode("&", $data);
        }
    }
    private function arr2key($arr)
    {
        if (is_array($arr)) {
            ksort($arr);
            $data = [];
            foreach ($arr as $key => $val) {
                $data[] = strtolower(rawurlencode($key));
            }
            return implode(";", $data);
        }
    }
    private function getSignature($opt, $key, $method, $config)
    {
        $Str = $this->json2str($opt, 1);
        if ($Str) {
            $formatString = "{$method}{$config['domain']}/?{$Str}";
            $sign         = hash_hmac('sha1', $formatString, $key);
            $sign         = base64_encode($this->_hex2bin($sign));
            return $sign;
        }
    }
}
