<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2022-07-11 11:19:43
 * @Description: 用户管理
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class admin extends adminbase
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
                $where = $LC['name'] ? " AND (name LIKE :name OR title LIKE :name OR email LIKE :name OR mobile LIKE :name)" : "";
                if (LCMS::SUPER()) {
                    $data = TABLE::set("admin", "id != 0{$where}", "id ASC", [
                        ":name" => "%{$LC['name']}%",
                    ]);
                } else {
                    $data = TABLE::set("admin", "(lcms = :id OR id = :id){$where}", "id ASC", [
                        ":id"   => $_L['LCMSADMIN']['id'],
                        ":name" => "%{$LC['name']}%",
                    ]);
                }
                $adminlist = [];
                $levellist = [];
                foreach ($data as $index => $val) {
                    if (!$adminlist[$val['lcms']]) {
                        $adminlist[$val['lcms']] = sql_get(["admin", "id = '{$val['lcms']}'"]);
                    }
                    if (!$levellist[$val['type']]) {
                        $levellist[$val['type']] = sql_get(["admin_level", "id = '{$val['type']}'"]);
                    }
                    $admin        = $adminlist[$val['lcms']];
                    $level        = $levellist[$val['type']];
                    $data[$index] = array_merge($val, [
                        "lcms"   => $val['lcms'] == "0" ? "超级管理员" : "{$admin['title']} - [{$admin['name']}]",
                        "type"   => $val['type'] === "lcms" ? "超级权限" : "{$level['name']} - [ID:{$level['id']}]",
                        "status" => [
                            "type"  => "switch",
                            "url"   => "index&action=list-save",
                            "text"  => "启用|禁用",
                            "value" => $val['status'],
                        ],
                    ]);
                }
                TABLE::out($data);
                break;
            case 'list-save':
                if ($LF['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "禁止修改");
                    exit;
                }
                sql_update(["admin", [
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
                if ($LC['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "禁止删除");
                    exit;
                } elseif ($LC[0]['id']) {
                    foreach ($LC as $key => $val) {
                        if ($val['id'] == $_L['LCMSADMIN']['id']) {
                            ajaxout(0, "禁止删除");
                            exit;
                        }
                    }
                }
                if (TABLE::del("admin")) {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-删除用户-{$LC['name']}",
                    ]);
                    ajaxout(1, "删除成功", "reload");
                } else {
                    ajaxout(0, "删除失败");
                }
                break;
            case 'edit':
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => $_L['form']['id'],
                ]);
                $form['base'] = [
                    ["layui"      => "input", "title" => "账号",
                        "name"        => "LC[name]",
                        "value"       => $admin['name'],
                        "placeholder" => "帐号用来登录，不能重复",
                        "verify"      => "required|name",
                        "disabled"    => $admin['name'] && $admin['type'] != "lcms" ? "1" : "",
                    ],
                    ["layui"      => "input", "title" => "姓名",
                        "name"        => "LC[title]",
                        "value"       => $admin['title'],
                        "placeholder" => "姓名只做显示",
                        "verify"      => "required",
                    ],
                    ["layui"      => "input", "title" => "密码",
                        "name"        => "LC[pass]",
                        "value"       => $admin['pass'],
                        "placeholder" => "请输入用户密码",
                        "verify"      => "required",
                        "type"        => "password",
                    ],
                    ["layui"      => "input", "title" => "重复密码",
                        "name"        => "repass",
                        "value"       => $admin['pass'],
                        "placeholder" => "请再次输入用户密码",
                        "verify"      => "required|pass",
                        "type"        => "password",
                    ],
                    ["layui" => "input", "title" => "邮箱",
                        "name"   => "LC[email]",
                        "value"  => $admin['email'],
                        "type"   => "email",
                    ],
                    ["layui" => "input", "title" => "手机号",
                        "name"   => "LC[mobile]",
                        "value"  => $admin['mobile'],
                        "type"   => "phone",
                    ],
                    ["layui" => "radio", "title" => "账号状态",
                        "name"   => "LC[status]",
                        "value"  => $admin['status'] != null ? $admin['status'] : 0,
                        "radio"  => [
                            ["title" => "启用", "value" => 1],
                            ["title" => "禁用", "value" => 0],
                        ],
                    ],
                ];
                $form['level'] = [
                    ["layui" => "title", "title" => "权限设置"],
                    ["layui"  => "selectN", "title" => "用户权限",
                        "name"    => "admin_level",
                        "value"   => "{$admin['lcms']}/{$admin['type']}",
                        "tips"    => "先新建用户权限再选择",
                        "default" => "上级用户|用户权限",
                        "verify"  => "required",
                        "url"     => "select&action=admin-level",
                    ],
                    ["layui" => "date", "title" => "到期时间",
                        "name"   => "LC[lasttime]",
                        "value"  => $admin['lasttime'],
                        "tips"   => "到期不能登录，为空不限制",
                        "min"    => datenow(),
                        "max"    => LCMS::SUPER() ? "" : ($_L['LCMSADMIN']['lasttime'] ? $_L['LCMSADMIN']['lasttime'] : ""),
                    ],
                ];
                require LCMS::template("own/admin/edit");
                break;
            case 'save':
                if ($LC['id'] == $_L['LCMSADMIN']['id']) {
                    unset($LC['status']);
                }
                if ($LC['oldpass'] != $LC['pass']) {
                    $LC['pass'] = md5($LC['pass']);
                }
                if ($LC['id']) {
                    if (!LCMS::SUPER()) {
                        unset($LC['name']);
                    }
                    $where = " AND id NOT IN({$LC['id']})";
                }
                if ($LC['name']) {
                    $admininfo = sql_get(["admin", "(name = :name OR email = :name OR mobile = :name){$where}", "id DESC", [
                        ":name" => $LC['name'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "账号已存在");
                    };
                }
                if ($LC['email']) {
                    $admininfo = sql_get(["admin", "(name = :email OR email = :email OR mobile = :email){$where}", "id DESC", [
                        ":email" => $LC['email'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "邮箱地址已存在");
                    };
                }
                if ($LC['mobile']) {
                    $admininfo = sql_get(["admin", "(name = :mobile OR email = :mobile OR mobile = :mobile){$where}", "id DESC", [
                        ":mobile" => $LC['mobile'],
                    ]]);
                    if ($admininfo) {
                        ajaxout(0, "手机号已存在");
                    };
                }
                if (!$LC['lasttime']) {
                    $LC['lasttime'] = null;
                }
                unset($LC['oldpass']);
                if ($LF['admin_level']) {
                    $level = explode("/", $_L['form']['admin_level']);
                    if (!$level[1]) {
                        ajaxout(0, "请设置用户权限");
                    } else {
                        $LC['lcms'] = $level[0];
                        $LC['type'] = $level[1];
                    }
                }
                if ($_L['LCMSADMIN']['lcms'] > "0") {
                    if ($_L['LCMSADMIN']['tuid'] == "0") {
                        $LC['tuid'] = $_L['LCMSADMIN']['id'];
                    } elseif ($_L['LCMSADMIN']['tuid'] > "0") {
                        $LC['tuid'] = $_L['LCMSADMIN']['tuid'];
                    }
                }
                LCMS::form([
                    "table" => "admin",
                    "form"  => $LC,
                ]);
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-" . ($LC['id'] ? "修改" : "添加") . "用户-{$LC['name']}",
                    ]);
                    ajaxout(1, "保存成功", "close");
                }
                break;
            case 'check-name':
                $admininfo = sql_get(["admin", "name = :name OR email = :name OR mobile = :name", "id DESC", [
                    ":name" => $_L['form']['name'],
                ]]);
                $admininfo && ajaxout(0, "账号已存在");
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 50],
                        ["title" => "ID", "field" => "id",
                            "width"  => 80,
                            "align"  => "center"],
                        ["title"   => "帐号", "field" => "name",
                            "minWidth" => 90],
                        ["title" => "姓名", "field" => "title",
                            "width"  => 150],
                        ["title" => "邮箱", "field" => "email",
                            "width"  => 200],
                        ["title" => "手机号", "field" => "mobile",
                            "width"  => 120],
                        ["title" => "用户权限", "field" => "type",
                            "width"  => 200],
                        ["title" => "上级用户", "field" => "lcms",
                            "width"  => 200],
                        ["title" => "到期时间", "field" => "lasttime",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title" => "账号状态", "field" => "status",
                            "width"  => 90,
                            "align"  => "center"],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 95,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
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
                        ["title" => "添加用户", "event" => "iframe",
                            "url"    => "index&action=edit",
                            "color"  => "default"],
                        ["title" => "批量删除", "event" => "ajax",
                            "url"    => "index&action=del",
                            "color"  => "danger",
                            "tips"   => "确认删除？"],
                    ],
                    "search"  => [
                        ["title" => "账号/姓名/邮箱/手机", "name" => "name"],
                    ],
                ];
                $acount = sql_counter(["admin"]);
                require LCMS::template("own/admin/list");
                break;
        }
    }
    /**
     * @description: 个人资料
     * @return {*}
     */
    public function doprofile()
    {
        global $_L, $LF, $LC;
        switch ($_L['form']['action']) {
            case 'admin-edit':
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => $_L['LCMSADMIN']['id'],
                ]);
                $form['base'] = [
                    ["layui"      => "input", "title" => "账号",
                        "name"        => "LC[name]",
                        "value"       => $admin['name'],
                        "placeholder" => "帐号用来登录，不能重复",
                        "verify"      => "required|name",
                        "disabled"    => $admin['name'] && $admin['type'] != "lcms" ? "1" : "",
                    ],
                    ["layui"      => "input", "title" => "姓名",
                        "name"        => "LC[title]",
                        "value"       => $admin['title'],
                        "placeholder" => "姓名只做显示",
                        "verify"      => "required",
                    ],
                    ["layui"      => "input", "title" => "密码",
                        "name"        => "LC[pass]",
                        "value"       => $admin['pass'],
                        "placeholder" => "请输入用户密码",
                        "verify"      => "required",
                        "type"        => "password",
                    ],
                    ["layui"      => "input", "title" => "重复密码",
                        "name"        => "repass",
                        "value"       => $admin['pass'],
                        "placeholder" => "请再次输入用户密码",
                        "verify"      => "required|pass",
                        "type"        => "password",
                    ],
                    ["layui"   => "input", "title" => "邮箱",
                        "name"     => "email",
                        "value"    => $admin['email'],
                        "type"     => "email",
                        "disabled" => $admin['type'] != "lcms" ? "1" : "",
                    ],
                    ["layui"   => "input", "title" => "手机号",
                        "name"     => "mobile",
                        "value"    => $admin['mobile'],
                        "type"     => "phone",
                        "disabled" => $admin['type'] != "lcms" ? "1" : "",
                    ],
                ];
                require LCMS::template("own/admin/edit");
                break;
            case 'save':
                if ($LC['oldpass'] != $LC['pass']) {
                    $LC['pass'] = md5($LC['pass']);
                }
                if ($LC['id'] && !LCMS::SUPER()) {
                    unset($LC['name']);
                }
                unset($LC['oldpass']);
                unset($LC['email']);
                unset($LC['mobile']);
                $_L['LCMSADMIN']['title'] = $LC['title'];
                SESSION::set("LCMSADMIN", $_L['LCMSADMIN']);
                LCMS::form([
                    "table" => "admin",
                    "form"  => $LC,
                ]);
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-修改资料",
                    ]);
                    ajaxout(1, "保存成功", "close");
                }
                break;
        }
    }
    /**
     * @description: 上帝视角
     * @return {*}
     */
    public function dogod()
    {
        global $_L, $LF, $LC;
        if (!LCMS::SUPER() && !$_L['LCMSADMIN']['god']) {
            LCMS::X(403, "没有权限访问");
        }
        switch ($LF['action']) {
            case 'list':
                $where = "status = 1 AND (lasttime IS NULL OR lasttime > '" . datenow() . "')";
                $where .= $LC['name'] ? " AND (name LIKE :name OR title LIKE :name OR email LIKE :name OR mobile LIKE :name)" : "";
                $data = TABLE::set("admin", $where, "id ASC", [
                    ":name" => "%{$LC['name']}%",
                ]);
                TABLE::out($data);
                break;
            case 'login':
                $admin = sql_get(["admin", "id = {$LC['id']}"]);
                if ($_L['LCMSADMIN']['god']) {
                    $admin['god'] = $_L['LCMSADMIN']['god'];
                } else {
                    $admin['god'] = $_L['LCMSADMIN']['id'];
                }
                SESSION::set("LCMSADMIN", $admin);
                ajaxout(2, "切换成功", "reloadall");
                break;
            default:
                $table = [
                    "url"    => "god&action=list",
                    "cols"   => [
                        ["title" => "ID", "field" => "id",
                            "width"  => 70,
                            "align"  => "center"],
                        ["title" => "帐号", "field" => "name",
                            "width"  => 180],
                        ["title"   => "姓名", "field" => "title",
                            "minWidth" => 100],
                        ["title" => "电话", "field" => "mobile",
                            "width"  => 120],
                        ["title"   => "邮箱", "field" => "eamil",
                            "minWidth" => 100],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 60,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title" => "登录",
                                    "event"  => "ajax",
                                    "url"    => "god&action=login",
                                    "color"  => "default",
                                    "tips"   => "确认登录到此用户？"],
                            ]],
                    ],
                    "search" => [
                        ["title" => "账号/姓名/邮箱/手机", "name" => "name"],
                    ],
                ];
                require LCMS::template("own/admin/god");
                break;
        }
    }
    /**
     * @description: 下拉选择AJAX数据
     * @param {*}
     * @return {*}
     */
    public function doselect()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'admin':
                $where = "status = 1 AND (lasttime IS NULL OR lasttime > '" . datenow() . "')";
                if (!LCMS::SUPER()) {
                    $where .= " AND id = '{$_L['LCMSADMIN']['id']}'";
                }
                $where .= $LF['keyword'] ? " AND (name LIKE :keyword OR title LIKE :keyword)" : "";
                $admin = sql_getall(["admin", $where, "id ASC", [
                    ":id"      => $LF['keyword'],
                    ":keyword" => "%{$LF['keyword']}%",
                ], "", "", 10]);
                foreach ($admin as $key => $val) {
                    $list[] = [
                        "value" => $val['id'],
                        "title" => "{$val['name']} - {$val['title']} - [ID:{$val['id']}]",
                    ];
                }
                ajaxout(1, "success", "", $list);
                break;
            case 'admin-level':
                $admin = $_L['LCMSADMIN'];
                $uid   = LCMS::SUPER() ? "uid != 0" : "uid = '{$admin['id']}'";
                $llist = sql_getall(["admin_level", $uid, "id ASC"]);
                $lids  = is_array($llist) ? implode(",", array_unique(array_column($llist, "uid"))) : "";
                $alist = $lids ? sql_getall(["admin", "id IN({$lids})", "id ASC"]) : [];
                foreach ($alist as $index => $val) {
                    $val['title'] .= " - [{$val['name']}]";
                    $val['title'] .= $val['lasttime'] > "0000-00-00 00:00:00" && $val['lasttime'] < datenow() ? " - 已到期" : "";
                    $arr[$index] = [
                        "value" => $val['type'] === "lcms" ? "0" : $val['id'],
                        "title" => $val['title'],
                    ];
                    foreach ($llist as $level) {
                        if ($level["uid"] == $val['id']) {
                            $arr[$index]['children'][] = [
                                "value" => $level['id'],
                                "title" => $level['name'] . " - [ID" . $level['id'] . "]",
                            ];
                        }
                    }
                }
                echo json_encode($arr ?: []);
                break;
        }
    }
}
