<?php
class TencentOSS
{
    public $cfg = [];
    public $api = "";
    public function __construct($config)
    {
        $this->cfg = $config;
        // 截取区域参数
        $this->cfg['Region'] = str_replace(["{$this->cfg['Bucket']}.cos.", ".myqcloud.com"], "", $this->cfg['Region']);
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
        $url     = $this->api;
        $headers = [
            "PUT"          => "/{$name} HTTP/1.1",
            "Host"         => $url,
            "Content-Type" => mime_content_type($file),
        ];
        $headers['Authorization'] = $this->sign("put", $name, [], $headers);

        $url = "https://" . $url . "/{$name}";
        if (!$this->request("PUT", $url, $body, $headers)) {
            return [
                "code" => 1,
                "msg"  => "success",
            ];
        }
    }
    /**
     * @description: 删除文件
     * @param {*} $file
     * @return {*}
     */
    public function delete($file)
    {
        $url     = $this->api;
        $headers = [
            "DELETE" => "/{$file} HTTP/1.1",
            "Host"   => $url,
        ];
        $headers['Authorization'] = $this->sign("delete", $file, [], $headers);

        $url = "https://" . $url . "/{$file}";
        $this->request("DELETE", $url, "", $headers);
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
    private function request($method = 'POST', $url, $data = "", $headers = [])
    {
        $ch = curl_init($url);
        if ($headers) {
            $header[] = "X-HTTP-Method-Override: {$method}";
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
