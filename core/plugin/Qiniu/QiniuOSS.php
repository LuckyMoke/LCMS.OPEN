<?php
class QiniuOSS
{
    public $cfg = [];
    public function __construct($config)
    {
        $this->cfg = $config;
    }
    /**
     * @description: 获取上传凭证
     * @return string
     */
    public function token()
    {
        $args = [
            "scope"    => $this->cfg['bucket'],
            "deadline" => time() + 3600,
        ];
        $encodedData = $this->base64Safe(json_encode($args));
        return $this->sign($encodedData) . ':' . $encodedData;
    }
    /**
     * @description: 文件上传
     * @param string $file
     * @return array
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
        $token  = $this->token();
        $time   = md5(microtime());
        $body   = file_get_contents($file);
        $data   = [];
        $data[] = "\"key\"\n\n{$name}";
        $data[] = "\"token\"\n\n{$token}";
        $data[] = "\"crc32\"\n\n" . $this->dataCrc32($body);
        $data[] = "\"file\"; filename=\"{$name}\"\nContent-Type: " . mime_content_type($file) . "\nContent-Transfer-Encoding: binary\n\n{$body}\n--{$time}--";
        $sepa   = "--{$time}\nContent-Disposition: form-data; name=";
        $data   = $sepa . implode("\n{$sepa}", $data);
        $result = HTTP::post("https://up-{$this->cfg['uphost']}.qiniup.com", $data, false, [
            "Content-Type" => "multipart/form-data; boundary={$time}",
        ]);
        $result = json_decode($result, true);
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => $result ? "SUCCESS" : $result['error'],
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
            $files[$index] = $this->base64Safe($this->cfg['bucket'] . ":{$file}");
        }
        return $this->httpPost("https://rs-{$this->cfg['uphost']}.qiniuapi.com/batch", "op=/delete/" . implode("&op=/delete/", $files));
    }
    /**
     * @description: 签名
     * @param string $data
     * @return string
     */
    private function sign($data)
    {
        return $this->cfg['AccessKey'] . ':' . $this->base64Safe(hash_hmac('sha1', $data, $this->cfg['secretKey'], true));
    }
    /**
     * @description: HTTP POST请求
     * @param string $url
     * @param string $body
     * @return array
     */
    private function httpPost($url, $body = null)
    {
        $urls = parse_url($url);
        $data = "POST ";
        $data .= $urls['path'] ?: "";
        $data .= $urls['query'] ? "?{$urls['query']}" : "";
        $data .= "\nHost: {$urls['host']}";
        $data .= "\nContent-Type: application/x-www-form-urlencoded\n\n";
        $data .= $body ?: "";
        $headers = [
            'Host'          => $urls['host'],
            'Content-Type'  => "application/x-www-form-urlencoded",
            'Authorization' => "Qiniu " . $this->sign($data),
        ];
        $result = HTTP::post($url, $body, false, $headers);
        $result = json_decode($result, true);
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => $result ? "SUCCESS" : $result['error'],
        ];
    }
    /**
     * @description: base64安全
     * @param string $str
     * @return string
     */
    private function base64Safe($str)
    {
        return str_replace([
            '+', '/',
        ], [
            '-', '_',
        ], base64_encode($str));
    }
    /**
     * @description: 字符串转crc32
     * @param string $data
     * @return string
     */
    private function dataCrc32($data)
    {
        $hash  = hash('crc32b', $data);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }
}
