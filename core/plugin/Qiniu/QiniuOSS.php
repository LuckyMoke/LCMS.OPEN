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
     * @param {*}
     * @return {*}
     */
    public function token()
    {
        $args = [
            "scope"    => $this->cfg['bucket'],
            "deadline" => time() + 3600,
        ];
        $encodedData = $this->base64_safe(json_encode($args));
        return $this->sign($encodedData) . ':' . $encodedData;
    }
    /**
     * @description: 文件上传
     * @param {*} $file
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
        $token  = $this->token();
        $body   = file_get_contents($file);
        $fields = [
            'token' => $token,
            'key'   => $name,
            'crc32' => $this->crc32_data($body),
        ];
        $finfo = finfo_open(FILEINFO_MIME);
        $mime  = finfo_file($finfo, $file);
        finfo_close($finfo);
        $data         = [];
        $mimeBoundary = md5(microtime());
        foreach ($fields as $key => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$key\"");
            array_push($data, '');
            array_push($data, $val);
        }
        array_push($data, '--' . $mimeBoundary);
        array_push($data, "Content-Disposition: form-data; name=\"file\"; filename=\"$name\"");
        array_push($data, "Content-Type: " . explode(";", $mime)[0]);
        array_push($data, '');
        array_push($data, $body);
        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');
        $body = implode("\r\n", $data);

        $contentType             = 'multipart/form-data; boundary=' . $mimeBoundary;
        $headers['Content-Type'] = $contentType;

        return $this->sendRequest("POST", "https://" . $this->upHost(), $body, [
            "Content-Type" => "multipart/form-data; boundary={$mimeBoundary}",
        ]);
    }
    /**
     * @description: 删除指定文件
     * @param {*} $file
     * @return {*}
     */
    public function delete($file)
    {
        $path = $this->base64_safe($this->cfg['bucket'] . ":{$file}");
        return $this->post("https://rs.qiniu.com/delete/{$path}");
    }
    /**
     * @description: 签名
     * @param {*} $data
     * @return {*}
     */
    private function sign($data)
    {
        return $this->cfg['AccessKey'] . ':' . $this->base64_safe(hash_hmac('sha1', $data, $this->cfg['secretKey'], true));
    }
    private function post($url, $body = null)
    {
        $headers = $this->authorization($url, $body);
        return $this->sendRequest("POST", $url, $body, $headers);
    }
    private function authorization($urlString, $body = null, $contentType = null)
    {
        $url  = parse_url($urlString);
        $data = '';
        if (array_key_exists('path', $url)) {
            $data = $url['path'];
        }
        if (array_key_exists('query', $url)) {
            $data .= '?' . $url['query'];
        }
        $data .= "\n";
        if ($body !== null && $contentType === 'application/x-www-form-urlencoded') {
            $data .= $body;
        }
        return [
            'Authorization' => 'QBox ' . $this->sign($data),
        ];
    }
    private function base64_safe($str)
    {
        return str_replace([
            '+', '/',
        ], [
            '-', '_',
        ], base64_encode($str));
    }
    private function crc32_data($data)
    {
        $hash  = hash('crc32b', $data);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }
    private function upHost()
    {
        switch ($this->cfg['uphost']) {
            case 'hd':
                return 'up.qiniup.com';
                break;
            case 'hb':
                return 'up-z1.qiniup.com';
                break;
            case 'hn':
                return 'up-z2.qiniup.com';
                break;
        }
    }
    private function sendRequest($method = "POST", $url, $body = null, $headers = [])
    {
        $t1 = microtime(true);
        $ch = curl_init();
        if (!empty($headers)) {
            foreach ($headers as $key => $val) {
                $headers[$key] = "$key: $val";
            }
            $headers = array_values($headers);
        }
        $options = [
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER         => false,
            CURLOPT_NOBODY         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);
        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $options);
        $ret  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return [
                "code" => 1,
                "msg"  => "SUCCESS",
            ];
        } else {
            $ret = json_decode($ret, true);
            return [
                "code" => 0,
                "msg"  => $ret['error'],
            ];
        }
    }
}
