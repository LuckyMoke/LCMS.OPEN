<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2024-02-01 13:21:10
 * @Description: 用户管理
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
load::own_class('pub');
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
                if ($LC['name']) {
                    if (is_email($LC['name'])) {
                        //邮箱
                        $where = "email = :id";
                    } elseif (is_phone($LC['name'])) {
                        //手机号
                        $where = "mobile = :id";
                    } else {
                        //ID、账号、姓名
                        $where = "id = :id OR name LIKE :name OR title LIKE :name";
                    }
                }
                if (LCMS::SUPER()) {
                    $data = TABLE::set("admin", $where, "id ASC", [
                        ":id"   => $LC['name'],
                        ":name" => "%{$LC['name']}%",
                    ]);
                } else {
                    $data = TABLE::set("admin", "lcms = :lcms" . ($where ? " AND ({$where})" : ""), "id ASC", [
                        ":id"   => $LC['name'],
                        ":name" => "%{$LC['name']}%",
                        ":lcms" => $_L['LCMSADMIN']['id'],
                    ]);
                    if (!$where) {
                        $data = array_merge([$_L['LCMSADMIN']], $data ?: []);
                    }
                }
                $adminlist = [];
                $levellist = [];
                foreach ($data as $index => $val) {
                    $val = array_merge($val, sql2arr($val['parameter']));
                    if (!$adminlist[$val['lcms']]) {
                        $adminlist[$val['lcms']] = sql_get(["admin", "id = '{$val['lcms']}'"]);
                    }
                    if (!$levellist[$val['type']]) {
                        $levellist[$val['type']] = sql_get(["admin_level", "id = '{$val['type']}'"]);
                    }
                    $admin = $adminlist[$val['lcms']];
                    $level = $levellist[$val['type']];
                    $token = PUB::id2token($val['id']);
                    if ($val['lcms'] == 0) {
                        if ($val['storage'] > 0) {
                            $storage = number_format($val['storage_used'] / $val['storage'] * 100, 2);
                            $storage = $storage > 100 ? 100 : $storage;
                        } else {
                            $storage = 100;
                        }
                        $storage .= "%";
                    } else {
                        $storage = null;
                    }
                    $data[$index] = array_merge($val, [
                        "token"    => $token,
                        "headimg"  => [
                            "type"   => "image",
                            "width"  => "auto",
                            "height" => "100%",
                            "src"    => $val['headimg'] ?: "../public/static/images/headimg.png",
                        ],
                        "lcms"     => $val['lcms'] == 0 ? "超级管理员" : "{$admin['title']} - [{$admin['name']}]",
                        "type"     => $val['type'] === "lcms" ? "超级权限" : [
                            "type"  => "link",
                            "title" => $val['level'] ? "自定义权限" : "{$level['name']} - [ID:{$level['id']}]",
                            "url"   => "javascript:setPower('{$token}')",
                        ],
                        "status"   => [
                            "type"  => "switch",
                            "url"   => "index&action=list-save&token={$token}",
                            "text"  => "启用|禁用",
                            "value" => $val['status'],
                        ],
                        "email"    => $val['email'] ? $val['email'] : '<span style="color:#cccccc">无</span>',
                        "mobile"   => $val['mobile'] ? $val['mobile'] : '<span style="color:#cccccc">无</span>',
                        "lasttime" => $val['lasttime'] ? ($val['lasttime'] > datenow() ? $val['lasttime'] : '<span style="color:red">' . $val['lasttime'] . '</span>') : '<span style="color:#cccccc">永久</span>',
                        "storage"  => $storage ? '<div class="layui-progress" style="top:70%;transform:translateY(-50%)">
                        <div class="layui-progress-bar" style="background:#909399;width:' . $storage . '"><span class="layui-progress-text" style="cursor:pointer;top:-24px" onclick="changeStorage(\'' . $token . '\')"><i class="layui-icon layui-icon-chart"></i>' . ($val['storage'] == 0 ? "无限" : intval($val['storage_used'] / 1024) . "/" . intval($val['storage'] / 1024) . "MB") . '</span></div></div>' : '<span style="color:#cccccc">同上级用户</span>',
                    ]);
                    unset($val['pass'], $val['salt'], $val['parameter'], $val['level']);
                }
                TABLE::out($data);
                break;
            case 'list-save':
                if ($LF['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "禁止修改");
                }
                sql_update(["admin", [
                    "status" => $LC['value'] > 0 ? 1 : 0,
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
                $LC     = $LC['id'] ? [$LC] : $LC;
                $names  = implode("、", array_column($LC, "name"));
                $tokens = array_column($LC, "token");
                $ids    = [];
                foreach ($tokens as $token) {
                    $ids[] = PUB::token2id($token);
                }
                if (in_array($_L['LCMSADMIN']['id'], $ids)) {
                    ajaxout(0, "禁止删除自己");
                }
                $ids = implode(",", $ids);
                $ids && sql_delete([
                    "table" => "admin",
                    "where" => "id IN({$ids})",
                ]);
                if (!sql_error()) {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-删除用户-{$names}",
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
                    "id"    => PUB::token2id($LF['token']),
                ]);
                $form['base'] = [
                    ["layui"    => "upload", "title" => "头像",
                        "name"      => "LC[headimg]",
                        "value"     => $admin['headimg'],
                        "maxwidth"  => 200,
                        "maxheight" => 200,
                        "tips"      => "请上传200*200尺寸以内正方形图片"],
                    ["layui"      => "input", "title" => "账号",
                        "name"        => "LC[name]",
                        "value"       => $admin['name'],
                        "placeholder" => "登录账号不能重复",
                        "verify"      => "required",
                        "disabled"    => $admin['name'] && $admin['type'] != "lcms" && $admin['id'] == $_L['LCMSADMIN']['id'] ? true : false],
                    ["layui"      => "input", "title" => "姓名",
                        "name"        => "LC[title]",
                        "value"       => $admin['title'],
                        "placeholder" => "姓名只做后台显示",
                        "verify"      => "required"],
                    ["layui"      => "input", "title" => "密码",
                        "name"        => "LC[pass]",
                        "placeholder" => $admin ? "请输入要修改的新密码" : "",
                        "verify"      => $admin ?: "required",
                        "type"        => "password"],
                    ["layui"      => "input", "title" => "邮箱",
                        "name"        => "LC[email]",
                        "value"       => $admin['email'],
                        "type"        => "email",
                        "placeholder" => "[非必填] 邮箱不能重复"],
                    ["layui"      => "input", "title" => "手机",
                        "name"        => "LC[mobile]",
                        "value"       => $admin['mobile'],
                        "type"        => "number",
                        "placeholder" => "[非必填] 手机号不能重复"],
                    ["layui" => "radio", "title" => "状态",
                        "name"   => "LC[status]",
                        "value"  => $admin['status'] ?? 1,
                        "radio"  => [
                            ["title" => "启用", "value" => 1],
                            ["title" => "禁用", "value" => 0],
                        ]],
                ];
                $form['level'] = [
                    ["layui" => "title", "title" => "权限设置"],
                    ["layui" => "des", "title" => "如“用户权限”中无可选择项，请先到“权限管理”里新建权限。<br>如需单独给某个用户自定义权限，可在添加用户后，点击用户列表中对应权限列，进行权限自定义。"],
                    ["layui"  => "selectN", "title" => "用户权限",
                        "name"    => "admin_level",
                        "value"   => "{$admin['lcms']}/{$admin['type']}",
                        "default" => "请选择|请选择",
                        "verify"  => "required",
                        "url"     => "select&action=admin-level"],
                    ["layui" => "date", "title" => "到期时间",
                        "name"   => "LC[lasttime]",
                        "value"  => $admin['lasttime'],
                        "tips"   => "到期不能登录，为空不限制",
                        "min"    => datenow(),
                        "max"    => LCMS::SUPER() ? "" : ($_L['LCMSADMIN']['lasttime'] ? $_L['LCMSADMIN']['lasttime'] : "")],
                ];
                require LCMS::template("own/admin/edit");
                break;
            case 'save':
                PUB::userSave(["id", "headimg", "name", "title", "pass", "email", "mobile", "status", "lasttime", "addtime"]);
                break;
            case 'storage-edit':
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => PUB::token2id($LF['token']),
                ]);
                if ($admin['type'] == "lcms" || $admin['id'] == $_L['LCMSADMIN']['id']) {
                    LCMS::X(403, "禁止修改");
                }
                $form = [
                    ["layui" => "html", "title" => "当前账号",
                        "value"  => "{$admin['name']} - {$admin['title']} - [ID:{$admin['id']}]",
                        "nodrop" => true],
                    ["layui" => "input", "title" => "存储上限/单位MB",
                        "name"   => "LC[storage]",
                        "value"  => intval($admin['storage'] / 1024),
                        "type"   => "number",
                        "verify" => "required",
                        "tips"   => "0为无限，单位MB",
                        "min"    => 0,
                        "max"    => 1048576,
                        "step"   => 1],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/admin/storage");
                break;
            case 'storage-save':
                $LC['storage'] = intval(abs($LC['storage']) * 1024);
                PUB::userSave(["id", "storage"]);
                break;
            case 'power-edit':
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => PUB::token2id($LF['token']),
                ]);
                list($level, $hide) = PUB::getLevelList($admin['level'] ?: LCMS::form([
                    "do"    => "get",
                    "table" => "admin_level",
                    "id"    => $admin['type'],
                ]));
                $form = [
                    ["layui"  => "selectN", "title" => "用户权限",
                        "name"    => "admin_level",
                        "value"   => "{$admin['lcms']}/{$admin['type']}",
                        "default" => "请选择|请选择",
                        "verify"  => "required",
                        "url"     => "select&action=admin-level"],
                    ["layui" => "radio", "title" => "另自定义",
                        "name"   => "custom",
                        "value"  => $admin['level'] ? 1 : 0,
                        "radio"  => [
                            ["title" => "使用上方所选权限", "value" => 0, "tab" => "custom0"],
                            ["title" => "我要完全自定义权限", "value" => 1, "tab" => "custom1"],
                        ]],
                    ["layui" => "des", "title" => "点击左侧应用名称、或者点击每个小模块的标题，均可进行全选操作！", "cname" => "hidden custom1"],
                ];
                $isadmin = true;
                require LCMS::template("own/power/edit");
                break;
            case 'power-save':
                if ($LF['custom'] > 0) {
                    unset($LC['uid']);
                    $LC = [
                        "level" => $LC,
                    ];
                } else {
                    $LC = [
                        "level" => [],
                    ];
                }
                PUB::userSave(["id", "level"]);
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 50],
                        ["title" => "ID", "field" => "id",
                            "width"  => 70,
                            "align"  => "center"],
                        ["title" => "头像", "field" => "headimg",
                            "width"  => 50,
                            "align"  => "center"],
                        ["title"   => "账号", "field" => "name",
                            "minWidth" => 90],
                        ["title" => "姓名", "field" => "title",
                            "width"  => 150],
                        ["title" => "邮箱", "field" => "email",
                            "width"  => 150],
                        ["title" => "手机", "field" => "mobile",
                            "width"  => 120],
                        ["title" => "用户权限", "field" => "type",
                            "width"  => 200],
                        ["title" => "上级用户", "field" => "lcms",
                            "width"  => 160],
                        ["title" => "到期时间", "field" => "lasttime",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title" => "存储空间", "field" => "storage",
                            "width"  => 150,
                            "align"  => "center"],
                        ["title" => "状态", "field" => "status",
                            "width"  => 90,
                            "align"  => "center"],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 95,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title" => "编辑", "event" => "iframe",
                                    "url"    => "index&action=edit&token={token}",
                                    "color"  => "default"],
                                ["title" => "删除", "event" => "ajax",
                                    "url"    => "index&action=del",
                                    "color"  => "danger",
                                    "tips"   => "删除用户会导致此用户内的所有数据丢失，一般禁用即可，真的要删除用户[{name}]？"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => "添加用户", "event" => "iframe",
                            "url"    => "index&action=edit",
                            "color"  => "default"],
                        ["title" => "批量删除", "event" => "ajax",
                            "url"    => "index&action=del",
                            "color"  => "danger",
                            "tips"   => "删除用户会导致此用户内的所有数据丢失，一般禁用即可，真的要删除？"],
                    ],
                    "search"  => [
                        ["title" => "ID/账号/姓名/邮箱/手机", "name" => "name"],
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
        $nopower = true;
        $admin   = LCMS::form([
            "do"    => "get",
            "table" => "admin",
            "id"    => $_L['LCMSADMIN']['id'],
        ]);
        $form['base'] = [
            ["layui"    => "upload", "title" => "头像",
                "name"      => "LC[headimg]",
                "value"     => $admin['headimg'],
                "maxwidth"  => 200,
                "maxheight" => 200,
                "tips"      => "请上传200*200尺寸以内正方形图片"],
            ["layui" => "html", "title" => "账号",
                "name"   => "LC[name]",
                "value"  => $admin['name'],
                "nodrop" => true],
            ["layui"      => "input", "title" => "姓名",
                "name"        => "LC[title]",
                "value"       => $admin['title'],
                "placeholder" => "姓名只做显示",
                "verify"      => "required"],
            ["layui"      => "input", "title" => "密码",
                "name"        => "LC[pass]",
                "placeholder" => "请输入要修改的新密码",
                "type"        => "password"],
            ["layui" => "html", "title" => "邮箱",
                "name"   => "email",
                "value"  => $admin['email'] ?: "无",
                "nodrop" => true],
            ["layui" => "html", "title" => "手机",
                "name"   => "mobile",
                "value"  => $admin['mobile'] ?: "无",
                "nodrop" => true],
        ];
        $LF['token'] = PUB::id2token($admin['id']);
        require LCMS::template("own/admin/edit");
    }
    /**
     * @description: 上帝视角
     * @return {*}
     */
    public function dogod()
    {
        global $_L, $LF, $LC;
        if (!LCMS::SUPER() && !$_L['LCMSADMIN']['god']) {
            LCMS::X(403, "此功能仅超级管理员可用");
        }
        switch ($LF['action']) {
            case 'list':
                $where = "status = 1 AND (lasttime IS NULL OR lasttime > :lasttime)";
                if ($LC['name']) {
                    $where .= " AND ";
                    if (is_email($LC['name'])) {
                        //邮箱
                        $where .= "email = :id";
                    } elseif (is_phone($LC['name'])) {
                        //手机号
                        $where .= "mobile = :id";
                    } else {
                        //ID、账号、姓名
                        $where = "(id = :id OR name LIKE :name OR title LIKE :name)";
                    }
                }
                $data = TABLE::set("admin", $where, "id ASC", [
                    ":lasttime" => datenow(),
                    ":id"       => $LC['name'],
                    ":name"     => "%{$LC['name']}%",
                ]);
                foreach ($data as $index => $val) {
                    unset($val['pass'], $val['salt'], $val['parameter']);
                    $data[$index] = array_merge($val, [
                        "headimg" => [
                            "type"   => "image",
                            "width"  => "auto",
                            "height" => "100%",
                            "src"    => $val['headimg'] ?: "../public/static/images/headimg.png",
                        ],
                        "email"   => $val['email'] ? $val['email'] : '<span style="color:#cccccc">无</span>',
                        "mobile"  => $val['mobile'] ? $val['mobile'] : '<span style="color:#cccccc">无</span>',
                    ]);
                }
                TABLE::out($data);
                break;
            case 'login':
                $admin = sql_get(["admin", "id = {$LC['id']}"]);
                if ($_L['LCMSADMIN']['god']) {
                    $admin['god'] = $_L['LCMSADMIN']['god'];
                    if ($admin['type'] == "lcms") {
                        unset($admin['god']);
                    }
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
                        ["title" => "头像", "field" => "headimg",
                            "width"  => 50,
                            "align"  => "center"],
                        ["title" => "账号", "field" => "name",
                            "width"  => 180],
                        ["title"   => "姓名", "field" => "title",
                            "minWidth" => 100],
                        ["title" => "电话", "field" => "mobile",
                            "width"  => 120],
                        ["title"   => "邮箱", "field" => "email",
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
                                    "tips"   => "确认登录到用户[{name}]？"],
                            ]],
                    ],
                    "search" => [
                        ["title" => "ID/账号/姓名/邮箱/手机", "name" => "name"],
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
                                "title" => $level['name'] . " - [ID:" . $level['id'] . "]",
                            ];
                        }
                    }
                }
                echo json_encode($arr ?: []);
                break;
        }
    }
}
