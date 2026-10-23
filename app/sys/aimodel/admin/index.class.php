<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2024-05-27 11:11:38
 * @LastEditTime: 2026-10-23 17:35:32
 * @Description: AI大模型
 * Copyright 2024 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        require LCMS::template("own/index");
    }
    public function doconfig()
    {
        global $_L, $LF, $LC;
        $aicfg = $_L['plugin']['aimodel'];
        if ($aicfg['on'] != 1) $this->ajaxerror();
        $thinking = false;
        switch ($LF['action']) {
            case 'chat':
                $plg   = $aicfg[$aicfg['chat']];
                $model = $plg['model'];
                $model = explode("|", $model);
                if ($model[1] && $model[1] == "thinking") {
                    $plg['model'] = $model[0];
                    $thinking     = true;
                }
                switch ($aicfg['chat']) {
                    case 'aliyun':
                        $result = [
                            "name"  => "aliyun",
                            "api"   => "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'huoshan':
                        $result = [
                            "name"  => "huoshan",
                            "api"   => "https://ark.cn-beijing.volces.com/api/v3/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'deepseek':
                        $result = [
                            "name"  => "deepseek",
                            "api"   => "https://api.deepseek.com/chat/completions",
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    case 'openai':
                        $result = [
                            "name"  => "openai",
                            "api"   => $plg['api'],
                            "model" => $plg['model'],
                            "token" => $plg['token'],
                        ];
                        break;
                    default:
                        $this->ajaxerror();
                        break;
                }
                break;
            case 'code':
                $plg = $aicfg['code'];
                if (!$plg['token']) $this->ajaxerror();
                switch ($plg['type']) {
                    case 'deepseek':
                        $result = [
                            "name"  => "code",
                            "api"   => "https://api.deepseek.com/chat/completions",
                            "model" => $_L['developer']['aimodel']['code'] ?: "deepseek-flash",
                            "token" => $plg['token'],
                        ];
                        break;
                    default:
                        $result = [
                            "name" => "code",
                            "api"  => "{$_L['url']['own_form']}proxy&api=" . urlencode("https://open.bigmodel.cn/api/paas/v4/chat/completions") . "&token=" . urlencode($plg['token']),
                            "model" => $_L['developer']['aimodel']['code'] ?: "glm-5.3-flash",
                            "token" => $plg['token'],
                        ];
                        break;
                }
                break;
        }
        $result['thinking'] = $thinking;
        ajaxout(1, "success", "", $result);
    }
    /**
     * @description: 代理请求
     * @return {*}
     */
    public function doproxy()
    {
        global $_L, $LF, $LC;
        @ini_set("display_errors", "0");
        error_reporting(0);
        @ini_set("zlib.output_compression", "0");
        @ini_set("output_buffering", "0");
        @ini_set("implicit_flush", "1");
        @ini_set("default_socket_timeout", "600");
        if (function_exists("set_time_limit")) {
            @set_time_limit(0);
        } elseif (function_exists("ini_set")) {
            @ini_set("max_execution_time", "0");
        } while (ob_get_level() > 0)@ob_end_clean();
        header("Content-Type: text/event-stream; charset=utf-8");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("X-Accel-Buffering: no");
        header("Connection: keep-alive");
        function sseError($message, $extra = [])
        {
            $payload = array_merge([
                "error" => $message,
            ], $extra);
            $data = json_encode($payload);
            return "event: error\ndata: {$data}\n\n";
        }
        function proxySubstr($str, $len)
        {
            return function_exists('mb_substr') ? mb_substr($str, 0, $len) : substr($str, 0, $len);
        }
        function sseFail($message, $extra = [])
        {
            echo sseError($message, $extra);
            @ob_flush();
            @flush();
            exit;
        }
        $apiUrl  = $LF['api'] ?? "";
        $apiKey  = $LF['token'] ?? "";
        $payload = file_get_contents("php://input");
        if (!$apiUrl || !$apiKey || !$payload) {
            sseFail("缺少参数");
        }
        echo ": ping " . time() . "\n\n";
        @ob_flush();
        @flush();
        $upstreamStatus = 0;
        $mode           = "stream";
        $errorBody      = "";
        $buffer         = "";
        $tail32         = "";
        $fatal          = false;
        $ch             = curl_init($apiUrl);
        $curlOpts       = [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "Accept: text/event-stream",
                "Authorization: Bearer {$apiKey}",
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, $hdr) use (&$upstreamStatus) {
                if (preg_match("#^HTTP/[\d.]+\s+(\d{3})#i", trim($hdr), $m)) {
                    $upstreamStatus = (int) $m[1];
                }
                return strlen($hdr);
            },
            CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$buffer, &$errorBody, &$upstreamStatus, &$mode, &$tail32) {
                if ($upstreamStatus > 0 && ($upstreamStatus < 200 || ($upstreamStatus >= 300 && $upstreamStatus < 400))) {
                    return strlen($chunk);
                }
                if ($upstreamStatus >= 400 && $mode === "stream") {
                    $mode = "error";
                }
                if ($mode === "error") {
                    if (strlen($errorBody) < 65536) {
                        $errorBody .= $chunk;
                    }
                } else {
                    $buffer .= $chunk;
                    $tail32 = substr($tail32 . $chunk, -32);
                }
                return strlen($chunk);
            },
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
        ];
        if (defined('CURLOPT_TCP_NODELAY')) {
            $curlOpts[CURLOPT_TCP_NODELAY] = 1;
        }
        curl_setopt_array($ch, $curlOpts);
        $mh = curl_multi_init();
        if ($mh === false) {
            sseFail("curl_multi_init failed");
        }
        curl_multi_add_handle($mh, $ch);
        $heartbeat  = 15;
        $lastBeat   = microtime(true);
        $running    = null;
        $startTs    = microtime(true);
        $clientGone = false;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($status !== CURLM_OK) {
                echo sseError("curl_multi_exec failed: " . curl_multi_strerror($status));
                @ob_flush();
                @flush();
                $fatal = true;
                break;
            }
            if ($mode === "stream" && $buffer !== "") {
                echo $buffer;
                @ob_flush();
                @flush();
                $buffer   = "";
                $lastBeat = microtime(true);
            }
            if (connection_aborted()) {
                $clientGone = true;
                break;
            }
            if (microtime(true) - $lastBeat >= $heartbeat) {
                echo ": ping " . time() . "\n\n";
                @ob_flush();
                @flush();
                $lastBeat = microtime(true);
            }
            if ($running > 0 && curl_multi_select($mh, 0.2) === -1) {
                usleep(10000);
            }
        } while ($running > 0);
        if (!$fatal && !$clientGone && $mode === "stream" && $buffer !== "") {
            echo $buffer;
            @ob_flush();
            @flush();
        }
        if ($clientGone) {
        } elseif ($fatal) {
        } elseif ($mode === "error" || $upstreamStatus >= 400 || curl_errno($ch) !== 0) {
            echo sseError(
                $upstreamStatus >= 400
                ? ("upstream http " . $upstreamStatus)
                : ("upstream connect failed: " . (curl_error($ch) ?: "unknown error")),
                [
                    "http_code"  => $upstreamStatus,
                    "curl_errno" => curl_errno($ch),
                    "curl_error" => curl_error($ch),
                    "body"       => proxySubstr($errorBody, 1000),
                    "elapsed"    => round(microtime(true) - $startTs, 2) . "s",
                ]
            );
        } elseif (preg_match("/data:\s*\[DONE\]\s*$/", $tail32)) {
        } else {
            echo "data: [DONE]\n\n";
        }
        @ob_flush();
        @flush();
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        curl_multi_close($mh);
        exit;
    }
    /**
     * @description: 输出错误信息
     * @return {*}
     */
    private function ajaxerror()
    {
        ajaxout(0, "AI接口未配置，请到设置->接口设置->AI接口中配置！");
    }
}
