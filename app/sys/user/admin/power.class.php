<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-07-11 10:46:13
 * @LastEditTime: 2023-05-26 11:24:15
 * @Description: 权限管理
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class power extends adminbase
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
        switch ($LF['action']) {
            case 'list':
                if (LCMS::SUPER()) {
                    $data = TABLE::set("admin_level", "", "id ASC", "", "id, name, uid");
                } else {
                    $data = TABLE::set("admin_level", "uid = '{$_L['LCMSADMIN']['id']}'", "id ASC", "", "id, name, uid");
                }
                $adminlist = [];
                foreach ($data as $index => $val) {
                    if (!$adminlist[$val['uid']]) {
                        $adminlist[$val['uid']] = sql_get([
                            "admin", "id = '{$val['uid']}'",
                        ]);
                    }
                    $admin        = $adminlist[$val['uid']];
                    $data[$index] = array_merge($val, [
                        "uid" => $admin['title'] . " - [" . $admin['name'] . "]",
                    ]);
                }
                TABLE::out($data);
                break;
            case 'list-save':
                sql_update(["admin_level", [
                    $LC['name'] => $LC['value'],
                ], "id = :id", [
                    ":id" => $LC['id'],
                ]]);
                if (sql_error()) {
                    ajaxout(0, "保存失败" . sql_error());
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'del':
                $adminlist = sql_getall(["admin",
                    "type = :type", "", [
                        ":type" => $LC['id'],
                    ]]);
                if ($adminlist) {
                    ajaxout(0, "有用户使用此权限");
                } else {
                    if (TABLE::del("admin_level")) {
                        LCMS::log([
                            "type" => "system",
                            "info" => "用户管理-删除权限-{$LC['name']}",
                        ]);
                        ajaxout(1, "删除成功", "reload");
                    } else {
                        ajaxout(0, "删除失败");
                    }
                }
                break;
            case 'edit':
                $level = $LF['id'] ? LCMS::form([
                    "do"    => "get",
                    "table" => "admin_level",
                    "id"    => $LF['id'],
                ]) : [];
                $level['sys'] && ksort($level['sys']);
                $level['open'] && ksort($level['open']);
                $appall = LEVEL::applist();
                foreach ($appall as $type => $val) {
                    foreach ($val as $name => $info) {
                        if (!empty($info['class'])) {
                            $level[$type][$name]['title'] = $info['info']['title'];
                            foreach ($info['class'] as $class => $val) {
                                if (!empty($val['level'])) {
                                    $level[$type][$name]['class'][$class]['title'] = $val['title'];
                                    foreach ($val['level'] as $key => $val) {
                                        if ($info['power'][$class][$key] != "no") {
                                            $level[$type][$name]['class'][$class]['select'][] = [
                                                "value" => $key,
                                                "title" => $val['title'],
                                            ];
                                        } else {
                                            $hide[$type][$name][$class][$key] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($level as $type => $list) {
                    foreach ($list as $name => $li) {
                        foreach ($li['class'] as $class => $val) {
                            if (empty($val['select'])) {
                                unset($level[$type][$name]['class'][$class]);
                                if (empty($level[$type][$name]['class'])) {
                                    unset($level[$type][$name]);
                                }
                            }
                        }
                    }
                }
                $form = [
                    ["layui"      => "input", "title" => "权限名",
                        "name"        => "LC[name]",
                        "value"       => $level['name'],
                        "placehplder" => "请输入权限名",
                        "verify"      => "required",
                    ],
                    ["layui"   => "select", "title" => "创建人",
                        "name"     => "LC[uid]",
                        "value"    => $level['uid'] ? $level['uid'] : $_L['LCMSADMIN']['id'],
                        "verify"   => "required",
                        "url"      => "select&c=admin&action=admin",
                        "default"  => "请输入账号名搜索更多",
                        "tips"     => "请输入账号名搜索更多",
                        "disabled" => LCMS::SUPER() ? "" : true,
                    ],
                    ["layui" => "des", "title" => "点击左侧应用名称、或者点击每个小模块的标题，均可进行全选操作！"],
                ];
                $hide = $hide ? htmlspecialchars(json_encode($hide)) : "";
                require LCMS::template("own/power/edit");
                break;
            case 'save':
                if ($LF['level']) {
                    $level = json_decode($LF['level'], true);
                    if (is_array($level)) {
                        $LC = array_merge_recursive($level, $LC);
                    }
                }
                LCMS::form([
                    "table" => "admin_level",
                    "unset" => true,
                    "form"  => $LC,
                ]);
                if (sql_error()) {
                    ajaxout(0, sql_error());
                } else {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-" . ($LC['id'] ? "修改" : "添加") . "权限-{$LC['name']}",
                    ]);
                    ajaxout(1, "保存成功", "close");
                }
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["title" => "ID", "field" => "id",
                            "width"  => 80,
                            "align"  => "center"],
                        ["title" => "权限名", "field" => "name",
                            "width"  => 200,
                            "edit"   => "text"],
                        ["title" => "添加人", "field" => "uid",
                            "width"  => 300],
                        ["title"   => "操作", "field" => "do",
                            "minWidth" => 90,
                            "fixed"    => "right",
                            "toolbar"  => [
                                ["title" => "编辑", "event" => "iframe",
                                    "url"    => "index&action=edit",
                                    "color"  => "default"],
                                ["title" => "删除", "event" => "ajax",
                                    "url"    => "index&action=del",
                                    "color"  => "danger",
                                    "tips"   => "确认删除？"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => "添加权限", "event" => "iframe",
                            "url"    => "index&action=edit",
                            "color"  => "default"],
                    ],
                ];
                require LCMS::template("own/power/list");
                break;
        }
    }
}
