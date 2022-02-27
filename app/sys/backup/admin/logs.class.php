<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-02-26 20:02:30
 * @LastEditTime: 2022-02-27 15:38:26
 * @Description: 系统日志
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class logs extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doajax()
    {
        global $_L, $LF, $LC;
        $where = LCMS::SUPER() ? "id != 0" : "lcms = :lcms";
        switch ($LF['type']) {
            case 'message':
                $title = "消息日志";
                $types = $this->getMessageType();
                break;
            default:
                $title = "操作日志";
                $types = $this->getType();
                break;
        }
        $cache = implode("','", array_keys($types));
        $cache = " AND type IN ('{$cache}')";
        switch ($LF['action']) {
            case 'delall':
                sql_delete(["log", "{$where}{$cache}", [
                    ":lcms" => $_L['ROOTID'],
                ]]);
                if (sql_error()) {
                    ajaxout(0, "清除失败：" . sql_error());
                } else {
                    LCMS::log([
                        "type" => "system",
                        "info" => "清除{$title}",
                    ]);
                    ajaxout(1, "清除{$title}成功", "reload");
                }
                break;
            default:
                $where .= $LC['user'] ? " AND user = :user" : "";
                $where .= $LC['type'] ? " AND type = :type" : $cache;
                $data = TABLE::set("log", $where, "id DESC", [
                    ":type" => $LC['type'],
                    ":user" => $LC['user'],
                    ":lcms" => $_L['ROOTID'],
                ]);
                foreach ($data as $index => $val) {
                    $data[$index] = array_merge($val, [
                        "type" => $types[$val['type']]['title'],
                    ]);
                }
                TABLE::out($data);
                break;
        }
    }
    public function doshow()
    {
        global $_L, $LF, $LC;
        $show = LCMS::form([
            "do"    => "get",
            "table" => "log",
            "id"    => $LF['id'],
        ]);
        switch ($LF['type']) {
            case 'message':
                $form = [
                    ["layui"   => "input", "title" => "IP地址",
                        "value"    => $show['ip'],
                        "disabled" => true],
                    ["layui"   => "input", "title" => "操作说明",
                        "value"    => $show['info'],
                        "disabled" => true],
                    ["layui"   => "input", "title" => "操作时间",
                        "value"    => $show['addtime'],
                        "disabled" => true],
                    ["layui"   => "textarea", "title" => "发送数据",
                        "value"    => json_encode_ex($show['postdata']),
                        "disabled" => true],
                ];
                require LCMS::template("own/logs/show");
                break;
        }
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        $table = [
            "url"     => "ajax",
            "cols"    => [
                ["title" => "ID", "field" => "id",
                    "width"  => 80,
                    "align"  => "center"],
                ["title" => "用户", "field" => "user",
                    "width"  => 150],
                ["title" => "类型", "field" => "type",
                    "width"  => 150,
                    "align"  => "center"],
                ["title"   => "操作说明", "field" => "info",
                    "minWidth" => 100],
                ["title" => "IP地址", "field" => "ip",
                    "width"  => 200,
                    "align"  => "center"],
                ["title" => "操作时间", "field" => "addtime",
                    "width"  => 170,
                    "align"  => "center"],
            ],
            "toolbar" => [
                ["title" => "清除日志",
                    "event"  => "ajax",
                    "url"    => "ajax&action=delall",
                    "color"  => "danger",
                    "tips"   => "确认清除？"],
            ],
            "search"  => [
                ["title" => "日志类型", "name" => "type",
                    "type"   => "select",
                    "option" => $this->getType()],
                ["title" => "用户", "name" => "user"],
            ],
        ];
        require LCMS::template("own/logs/index");
    }
    public function domessage()
    {
        global $_L, $LF, $LC;
        $table = [
            "url"     => "ajax&type=message",
            "cols"    => [
                ["title" => "ID", "field" => "id",
                    "width"  => 80,
                    "align"  => "center"],
                ["title" => "类型", "field" => "type",
                    "width"  => 150,
                    "align"  => "center"],
                ["title"   => "操作说明", "field" => "info",
                    "minWidth" => 100],
                ["title" => "IP地址", "field" => "ip",
                    "width"  => 200,
                    "align"  => "center"],
                ["title" => "操作时间", "field" => "addtime",
                    "width"  => 170,
                    "align"  => "center"],
                ["title"  => "操作", "field" => "do",
                    "width"   => 70,
                    "align"   => "center",
                    "fixed"   => "right",
                    "toolbar" => [
                        ["title" => "详情",
                            "event"  => "iframe",
                            "url"    => "show&type=message"],
                    ]],
            ],
            "toolbar" => [
                ["title" => "清除日志",
                    "event"  => "ajax",
                    "url"    => "ajax&action=delall&type=message",
                    "color"  => "danger",
                    "tips"   => "确认清除？"],
            ],
            "search"  => [
                ["title" => "日志类型", "name" => "type",
                    "type"   => "select",
                    "option" => $this->getMessageType()],
            ],
        ];
        require LCMS::template("own/logs/index");
    }
    /**
     * @description: 获取操作类型
     * @param string $type
     * @return string|array
     */
    private function getType($type = "")
    {
        $types = [
            "login"  => ["title" => "用户登录", "value" => "login"],
            "system" => ["title" => "系统管理", "value" => "system"],
            "other"  => ["title" => "其它操作", "value" => "other"],
        ];
        return $type ? $types[$type] : $types;
    }
    /**
     * @description: 获取消息类型
     * @param string $type
     * @return string|array
     */
    private function getMessageType($type = "")
    {
        $types = [
            "email" => ["title" => "邮件通知", "value" => "email"],
            "sms"   => ["title" => "短信通知", "value" => "sms"],
        ];
        return $type ? $types[$type] : $types;
    }
}
