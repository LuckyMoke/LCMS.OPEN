<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-09-18 13:34:12
 * @LastEditTime: 2020-12-28 23:51:14
 * @Description: 全局程序错误输出
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class developer
{
    public function __construct()
    {
        global $_L;
        $this->init();
    }
    public function init()
    {
        global $_L;
        set_exception_handler(function ($exception) {
            global $_L;
            if ($_L['config']['admin']['development']) {
                $message = $exception->getMessage();
                $traces  = $this->getTrace($exception->getTrace());
                if (stripos($message, "Too few arguments to") !== false) {
                    $msg  = $traces[0]['class'] ? "{$traces[0]['class']}{$traces[0]['type']}{$traces[0]['function']}" : $traces[0]['function'];
                    $main = [
                        "msg"  => "Too few arguments to function {$msg}()",
                        "path" => $traces[0]['path'],
                        "file" => $traces[0]['file'],
                        "line" => $traces[0]['line'],
                    ];
                    unset($traces[0]);
                    $traces = array_values($traces);
                } else {
                    $main = [
                        "msg"  => $message,
                        "path" => $exception->getFile(),
                        "file" => basename($exception->getFile()),
                        "line" => $exception->getLine(),
                    ];
                }
                $getLine = $this->getLine($main['path'], $main['line']);
                $error   = [
                    "this"  => [
                        "message" => $main['msg'],
                        "fanyi"   => $this->Fanyi($main['msg']),
                        "code"    => $exception->getCode(),
                        "path"    => $main['path'],
                        "file"    => $main['file'],
                        "line"    => $main['line'],
                        "start"   => $getLine['start'],
                        "content" => htmlspecialchars($getLine['content']),
                    ],
                    "trace" => $traces,
                ];
                ob_end_clean();
                require LCMS::template(PATH_PUBLIC . "ui/admin/error");
                exit();
            } else {
                LCMS::X(500, "程序致命错误<br/>查看详情需开启开发模式");
            }
        });
    }
    private function Fanyi($message)
    {
        $elist = [
            "Too few arguments to function" => "缺少参数的函数",
            "Call to "                      => "调用",
            "syntax error"                  => "语法错误",
            "undefined "                    => "未定义的",
            "not found"                     => "未找到",
            "function "                     => "函数",
            "class "                        => "类",
            "method "                       => "方法",
            "constant "                     => "常量",
            "unexpected "                   => "意外的",
        ];
        $message = str_ireplace(array_keys($elist), array_values($elist), $message);
        return $message;
    }
    private function getTrace($traces)
    {
        global $_L;
        $result = [];
        foreach ($traces as $key => $val) {
            $result[] = [
                "path"     => $val['file'],
                "file"     => basename($val['file']),
                "line"     => $val['line'],
                "class"    => $val['class'],
                "function" => $val['function'],
                "type"     => $val['type'],
            ];
        }
        return $result;
    }
    private function getLine($filename, $line)
    {
        global $_L;
        $file  = new SplFileObject($filename, "r+");
        $start = $line - 10;
        $start = $start >= 0 ? $start : $line;
        $file->seek($start);
        $result = "";
        for ($i = 0; $i < 20; $i++) {
            $result .= $file->current();
            $file->next();
        }
        return [
            "start"   => $start,
            "content" => rtrim($result, "\n"),
        ];

    }
}
new developer();
