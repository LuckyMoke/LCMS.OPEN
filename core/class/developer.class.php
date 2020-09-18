<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-09-18 13:34:12
 * @LastEditTime: 2020-09-18 15:59:35
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
                $getLine = $this->getLine($exception->getFile(), $exception->getLine());
                $error   = [
                    "this"  => [
                        "message" => $exception->getMessage(),
                        "fanyi"   => $this->Fanyi($exception->getMessage()),
                        "code"    => $exception->getCode(),
                        "path"    => $exception->getFile(),
                        "file"    => basename($exception->getFile()),
                        "line"    => $exception->getLine(),
                        "start"   => $getLine['start'],
                        "content" => $getLine['content'],
                    ],
                    "trace" => $this->getTrace($exception->getTrace()),
                ];
                require LCMS::template(PATH_PUBLIC . "ui/admin/error");
            } else {
                LCMS::X(500, "程序错误");
            }
        });
    }
    private function Fanyi($message)
    {
        $elist = [
            "Call to"   => "调用",
            "undefined" => "未定义的",
            "not found" => "未找到",
            "function"  => "函数",
            "class"     => "类",
            "method"    => "方法",
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
        $end   = $line + 10;
        $file->seek($start);
        $result = "";
        for ($i = 0; $i < $end; $i++) {
            $result .= $file->current();
            $file->next();
        }
        return [
            "start"   => $start,
            "content" => $result,
        ];
    }
}
new developer();
