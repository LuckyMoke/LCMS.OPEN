<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2022-04-09 17:38:37
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
        $table = [
            "url"     => "ajax&action=admin-list",
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
                            "url"    => "iframe&action=admin-edit",
                            "color"  => "default"],
                        ["title" => "删除", "event" => "ajax",
                            "url"    => "ajax&action=admin-list-del",
                            "color"  => "danger",
                            "tips"   => "确认删除？"],
                    ]],
            ],
            "toolbar" => [
                ["title" => "添加用户", "event" => "iframe",
                    "url"    => "iframe&action=admin-edit",
                    "color"  => "default"],
                ["title" => "批量删除", "event" => "ajax",
                    "url"    => "ajax&action=admin-list-del",
                    "color"  => "danger",
                    "tips"   => "确认删除？"],
            ],
            "search"  => [
                ["title" => "账号/姓名/邮箱/手机", "name" => "name"],
            ],
        ];
        $acount = sql_counter(["admin"]);
        require LCMS::template("own/admin-list");
    }
    public function dolevel()
    {
        global $_L, $LF, $LC;
        $table = [
            "url"     => "ajax&action=admin-level-list",
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
                            "url"    => "iframe&action=admin-level-edit",
                            "color"  => "default"],
                        ["title" => "删除", "event" => "ajax",
                            "url"    => "ajax&action=admin-level-list-del",
                            "color"  => "danger",
                            "tips"   => "确认删除？"],
                    ]],
            ],
            "toolbar" => [
                ["title" => "添加权限", "event" => "iframe",
                    "url"    => "iframe&action=admin-level-edit",
                    "color"  => "default"],
            ],
        ];
        require LCMS::template("own/admin-list");
    }
    public function doajax()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case 'admin-list':
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
                        "type"   => $val['type'] == "lcms" ? "超级权限" : "{$level['name']} - [ID:{$level['id']}]",
                        "status" => [
                            "type"  => "switch",
                            "url"   => "ajax&action=admin-list-save",
                            "text"  => "启用|禁用",
                            "value" => $val['status'],
                        ],
                    ]);
                }
                TABLE::out($data);
                break;
            case 'admin-list-save':
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
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'admin-list-del':
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
            case 'admin-level-list':
                if (LCMS::SUPER()) {
                    $data = TABLE::set("admin_level", "", "id ASC", "", "id, name, uid");
                } else {
                    $data = TABLE::set("admin_level", "uid = '{$_L['LCMSADMIN']['id']}'", "id ASC", "", "id, name, uid");
                }
                $adminlist = [];
                foreach ($data as $index => $val) {
                    if (!$adminlist[$val['uid']]) {
                        $adminlist[$val['uid']] = sql_get(["admin", "id = '{$val['uid']}'"]);
                    }
                    $admin        = $adminlist[$val['uid']];
                    $data[$index] = array_merge($val, [
                        "uid" => $admin['title'] . " - [" . $admin['name'] . "]",
                    ]);
                }
                TABLE::out($data);
                break;
            case 'admin-level-list-save':
                sql_update(["admin_level", [
                    $LC['name'] => $LC['value'],
                ], "id = :id", [
                    ":id" => $LC['id'],
                ]]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'admin-level-list-del':
                $adminlist = sql_getall(["admin", "type = '{$LC['id']}'"]);
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
        }
    }
    public function doiframe()
    {
        global $_L, $LF, $LC;
        switch ($_L['form']['action']) {
            case 'admin-edit':
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
                require LCMS::template("own/iframe/admin-edit");
                break;
            case 'admin-check-name':
                $admininfo = sql_get(["admin", "name = :name OR email = :name OR mobile = :name", "id DESC", [
                    ":name" => $_L['form']['name'],
                ]]);
                if ($admininfo) {
                    ajaxout(0, "账号已存在");
                };
                break;
            case 'admin-save':
                if ($LC['id'] == $_L['LCMSADMIN']['id']) {
                    unset($_L['form']['LC']['status']);
                }
                if ($LC['oldpass'] != $LC['pass']) {
                    $_L['form']['LC']['pass'] = md5($LC['pass']);
                }
                if ($LC['id']) {
                    if (!LCMS::SUPER()) {
                        unset($_L['form']['LC']['name']);
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
                    $_L['form']['LC']['lasttime'] = null;
                }
                unset($_L['form']['LC']['oldpass']);
                if ($_L['form']['admin_level']) {
                    $level = explode("/", $_L['form']['admin_level']);
                    if (!$level[1]) {
                        ajaxout(0, "请设置用户权限");
                    } else {
                        $_L['form']['LC']['lcms'] = $level[0];
                        $_L['form']['LC']['type'] = $level[1];
                    }
                }
                if ($_L['LCMSADMIN']['lcms'] > "0") {
                    if ($_L['LCMSADMIN']['tuid'] == "0") {
                        $_L['form']['LC']['tuid'] = $_L['LCMSADMIN']['id'];
                    } elseif ($_L['LCMSADMIN']['tuid'] > "0") {
                        $_L['form']['LC']['tuid'] = $_L['LCMSADMIN']['tuid'];
                    }
                }
                LCMS::form(["table" => "admin"]);
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
            case 'admin-level-edit':
                $level = $_L['form']['id'] ? LCMS::form([
                    "do"    => "get",
                    "table" => "admin_level",
                    "id"    => $_L['form']['id'],
                ]) : [];
                $level && ksort($level['sys']);
                $level && ksort($level['open']);
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
                                            $level[$type][$name]['class'][$class]['select'][] = array(
                                                "value" => $key,
                                                "title" => $val['title'],
                                            );
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
                        "url"      => "select&action=admin",
                        "default"  => "请输入账号名搜索更多",
                        "tips"     => "请输入账号名搜索更多",
                        "disabled" => LCMS::SUPER() ? "" : true,
                    ],
                    ["layui" => "des", "title" => "点击左侧应用名称、或者点击每个小模块的标题，均可进行全选操作！"],
                ];
                $hide = $hide ? base64_encode(json_encode($hide)) : "";
                require LCMS::template("own/iframe/admin-level-edit");
                break;
            case 'admin-level-save':
                if ($LF['level']) {
                    $level = json_decode(base64_decode($LF['level']), true);
                    if (is_array($level)) {
                        $_L['form']['LC'] = array_merge_recursive($level, $LC);
                    }
                }
                LCMS::form([
                    "table" => "admin_level",
                    "unset" => true,
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
        }
    }
    /**
     * @用户注册设置:
     * @param {type}
     * @return {type}
     */
    public function doconfig()
    {
        global $_L, $LF, $LC;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "没有权限，禁止访问");
        }
        switch ($_L['form']['action']) {
            case 'save':
                $level = explode("/", $LF['admin_level']);
                if ($level[0] !== "" && $level[1] !== "") {
                    $_L['form']['LC']['reg']['lcms']  = $level[0];
                    $_L['form']['LC']['reg']['level'] = $level[1];
                    LCMS::config([
                        "do"   => "save",
                        "type" => "sys",
                        "cate" => "admin",
                    ]);
                    ajaxout(1, "保存成功");
                } else {
                    ajaxout(0, "保存失败，请选择默认权限");
                }
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "admin",
                ]);
                $form = [
                    "base" => [
                        ["layui"   => "input", "title" => "登陆地址",
                            "name"     => "login_url",
                            "value"    => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login",
                            "disabled" => true,
                        ],
                        ["layui"   => "input", "title" => "注册地址",
                            "name"     => "login_url",
                            "value"    => "{$_L['url']['admin']}index.php?rootid={$_L['ROOTID']}&n=login&c=reg",
                            "disabled" => true,
                        ],
                        ["layui" => "title", "title" => "登陆设置"],
                        ["layui" => "des", "title" => "微信扫码登陆需安装《微信公众号管理》应用才可正常使用！"],
                        ["layui" => "radio", "title" => "微信扫码",
                            "name"   => "LC[reg][qrcode]",
                            "value"  => $config['reg']['qrcode'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0"],
                                ["title" => "开启", "value" => "1"],
                            ],
                        ],
                        ["layui" => "title", "title" => "注册设置"],
                        ["layui" => "radio", "title" => "用户注册",
                            "name"   => "LC[reg][on]",
                            "value"  => $config['reg']['on'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0", "tab" => "tab0"],
                                ["title" => "账号验证", "value" => "justuser", "tab" => "tab_justuser"],
                                ["title" => "邮箱验证", "value" => "email", "tab" => "tab_email"],
                                ["title" => "手机号验证", "value" => "mobile", "tab" => "tab_mobile"],
                            ],
                        ],
                        ["layui" => "radio", "title" => "找回密码",
                            "name"   => "LC[reg][findpass]",
                            "value"  => $config['reg']['findpass'] ?? "0",
                            "radio"  => [
                                ["title" => "关闭", "value" => "0"],
                                ["title" => "开启", "value" => "1"],
                            ],
                            "tips"   => "需配置邮箱或短信接口！",
                        ],
                        ["layui" => "radio", "title" => "注册审核",
                            "name"   => "LC[reg][status]",
                            "value"  => $config['reg']['status'] ?? "0",
                            "radio"  => [
                                ["title" => "手动审核", "value" => "0"],
                                ["title" => "自动审核", "value" => "1"],
                            ],
                        ],
                        ["layui" => "input", "title" => "短信ID",
                            "name"   => "LC[reg][sms_tplcode]",
                            "value"  => $config['reg']['sms_tplcode'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件",
                        ],
                        ["layui" => "input", "title" => "短信签名",
                            "name"   => "LC[reg][sms_signname]",
                            "value"  => $config['reg']['sms_signname'],
                            "cname"  => "hidden tab_mobile",
                            "tips"   => "请先到全局设置中配置短信插件",
                        ],
                        ["layui" => "selectN", "title" => "默认权限",
                            "name"   => "admin_level",
                            "value"  => "{$config['reg']['lcms']}/{$config['reg']['level']}",
                            "verify" => "required",
                            "url"    => "select&action=admin-level",
                        ],
                        ["layui"   => "checkbox", "title" => "注册字段",
                            "checkbox" => [
                                ["title" => "姓名",
                                    "name"   => "LC[reg][input_title]",
                                    "value"  => $config['reg']['input_title']],
                            ],
                        ],
                        ["layui" => "title", "title" => "用户协议"],
                    ],
                    "btn"  => [
                        ["layui" => "btn", "title" => "立即保存"],
                    ],
                ];
                $readme = [
                    "user"    => [
                        ["layui" => "editor", "title" => "内容",
                            "name"   => "LC[readme][user]",
                            "value"  => $config['readme']['user']],
                    ],
                    "privacy" => [
                        ["layui" => "editor", "title" => "内容",
                            "name"   => "LC[readme][privacy]",
                            "value"  => $config['readme']['privacy']],
                    ],
                ];
                require LCMS::template("own/admin-config");
                break;
        }
    }
    /**
     * @个人资料:
     * @param {type}
     * @return {type}
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
                require LCMS::template("own/iframe/admin-edit");
                break;
            case 'admin-save':
                if ($LC['oldpass'] != $LC['pass']) {
                    $_L['form']['LC']['pass'] = md5($LC['pass']);
                }
                if ($LC['id'] && !LCMS::SUPER()) {
                    unset($_L['form']['LC']['name']);
                }
                unset($_L['form']['LC']['oldpass']);
                unset($_L['form']['LC']['email']);
                unset($_L['form']['LC']['mobile']);
                $_L['LCMSADMIN']['title'] = $LC['title'];
                SESSION::set("LCMSADMIN", $_L['LCMSADMIN']);
                LCMS::form(["table" => "admin"]);
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
     * @上帝视角:
     * @param {type}
     * @return {type}
     */
    public function dogod()
    {
        global $_L, $LF, $LC;
        if (LCMS::SUPER() || $_L['LCMSADMIN']['god']) {
            switch ($LF['action']) {
                case 'save':
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
                    $form = [
                        ["layui"  => "select", "title" => "当前视角",
                            "name"    => "LC[id]",
                            "value"   => $_L['LCMSADMIN']['id'],
                            "verify"  => "required",
                            "tips"    => "输入帐号名搜索更多",
                            "default" => "输入帐号名搜索更多",
                            "url"     => "select&action=admin&all=true",
                        ],
                        ["layui" => "btn", "title" => "立即切换"],
                    ];
                    require LCMS::template("own/iframe/god");
                    break;
            }
        } else {
            LCMS::X(403, "没有权限访问");
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
                if (!LCMS::SUPER() && $LF['all'] != "true") {
                    $where .= " AND id = '{$_L['LCMSADMIN']['id']}'";
                }
                $where .= $LF['keyword'] ? " AND (name LIKE :keyword OR title LIKE :keyword)" : "";
                $admin = sql_getall([
                    "admin", $where, "id ASC",
                    [
                        ":id"      => $LF['keyword'],
                        ":keyword" => "%{$LF['keyword']}%",
                    ], "", "", 10,
                ]);
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
                        "value" => $val['type'] == "lcms" ? "0" : $val['id'],
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
