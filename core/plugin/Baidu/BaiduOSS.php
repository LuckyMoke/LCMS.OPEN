<?php
class BaiduOSS
{
    public $cfg = [];
    public $api = "";
    public function __construct($config)
    {
        $this->cfg = $config;
        //Region清理
        $this->cfg['Region'] = str_replace([
            "http://", "https://", "/",
        ], "", $this->cfg['Region']);
        //拼接接口地址
        $this->api = $this->cfg['Region'];
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
            "api"       => "https://{$this->api}",
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
        $sign   = $this->sign("PUT", "/" . str_replace(PATH_WEB, "", $file));
        $result = HTTP::put($sign['api'], $body, [
            "Host"           => $this->api,
            "Content-Type"   => mime_content_type($file),
            "Content-Length" => filesize($file),
            "Authorization"  => $sign['sign'],
            "x-bce-date"     => $sign['date'],
        ]);
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
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
        $sign = $this->sign("POST", "/", "delete=");
        HTTP::post($sign['api'], json_encode([
            "objects" => $files,
        ]), false, [
            "Host"          => $this->api,
            "Content-Type"  => "application/json",
            "Authorization" => $sign['sign'],
            "x-bce-date"    => $sign['date'],
        ]);
        return [
            "code" => HTTP::$INFO['http_code'] == 200 ? 1 : 0,
            "msg"  => "SUCCESS",
        ];
    }
    /**
     * @description: 签名
     * @param string $method
     * @param string $path
     * @param string $query
     * @return string
     */
    public function sign($method, $path, $query = "")
    {
        $date    = gmdate("Y-m-d\TH:i:s\Z");
        $ipnut   = "{$method}\n{$path}\n{$query}\nhost:{$this->api}";
        $signPre = "bce-auth-v1/{$this->cfg['AccessKey']}/{$date}/1800";
        $signKey = hash_hmac("sha256", $signPre, $this->cfg['SecretKey']);
        $query   = $query ? "?{$query}" : "";
        return [
            "api"  => "https://{$this->api}{$path}{$query}",
            "date" => $date,
            "sign" => "{$signPre}/host/" . hash_hmac("sha256", $ipnut, $signKey),
        ];
    }
}
