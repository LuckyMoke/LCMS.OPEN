<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2025-08-05 11:24:00
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
    public function doindex($cate = 0)
    {
        global $_L, $LF, $LC;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "此功能仅管理员可用");
        }
        switch ($LF['action']) {
            case 'list':
                $where[] = "cate = :cate";
                if (isset($LC['status'])) {
                    $where[] = "status = :status";
                }
                if ($LC['name']) {
                    if (is_email($LC['name'])) {
                        //邮箱
                        $where[] = "email = :id";
                    } elseif (is_phone($LC['name'])) {
                        //手机号
                        $where[] = "mobile = :id";
                    } else {
                        //ID、账号、姓名
                        $where[] = "(id = :id OR name LIKE :name OR title LIKE :name)";
                    }
                }
                $where = array_filter($where);
                if (!LCMS::SUPER()) {
                    $where[] = "id != :ownid";
                }
                $where[] = "lcms = :lcms";
                $where   = implode(" AND ", $where);
                $data    = TABLE::set([
                    "table" => "admin",
                    "where" => $where,
                    "order" => "id ASC",
                    "bind"  => [
                        ":id"     => $LC['name'],
                        ":ownid"  => $_L['LCMSADMIN']['id'],
                        ":name"   => "%{$LC['name']}%",
                        ":cate"   => $LF['cate'],
                        ":status" => $LC['status'],
                        ":lcms"   => $_L['ROOTID'],
                    ],
                ]);
                if (
                    !LCMS::SUPER() &&
                    !$where &&
                    $LF['page'] == 1
                ) {
                    $data = array_merge([$_L['LCMSADMIN']], $data ?: []);
                }
                $ucfg = LCMS::config([
                    "name" => "user",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => $_L['ROOTID'],
                ]);
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
                    $appcfg = LCMS::config([
                        "name" => "menu",
                        "type" => "sys",
                        "cate" => "admin",
                        "lcms" => $val['id'],
                    ]);
                    if (!$appcfg['default'] && $ucfg['reg']) {
                        $appcfg['default']['on'] = $ucfg['reg']['defaultapp'];
                    }
                    switch ($appcfg['default']['on']) {
                        case '1':
                            $appdef = "第一个应用";
                            break;
                        case '2':
                            $appdef = "指定应用";
                            break;
                        default:
                            $appdef = "欢迎页";
                            break;
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
                            "icon"  => '<img src="/public/static/images/icons/power.svg"/>',
                            "url"   => "javascript:setPower('{$token}')",
                        ],
                        "statusT"  => [
                            "type"  => "switch",
                            "url"   => "index&action=list-save&token={$token}",
                            "text"  => "启用|停用",
                            "value" => $val['status'],
                        ],
                        "email"    => $val['email'] ? $val['email'] : '<span style="color:#cccccc">无</span>',
                        "mobile"   => $val['mobile'] ? $val['mobile'] : '<span style="color:#cccccc">无</span>',
                        "lasttime" => $val['lasttime'] ? ($val['lasttime'] > datenow() ? $val['lasttime'] : '<span style="color:red">' . $val['lasttime'] . '</span>') : '<span style="color:#cccccc">永久</span>',
                        "storage"  => $storage ? '<div class="layui-progress" style="top:70%;transform:translateY(-50%)">
                        <div class="layui-progress-bar" style="background:#909399;width:' . $storage . '"><span class="layui-progress-text" style="cursor:pointer;top:-24px" onclick="changeStorage(\'' . $token . '\')">' . ($val['storage'] == 0 ? "无限" : intval($val['storage_used'] / 1024) . "/" . intval($val['storage'] / 1024) . "MB") . '</span></div></div>' : '<span style="color:#cccccc">同上级</span>',
                        "defapp"   => $val['type'] === "lcms" ? '<span style="color:#cccccc">欢迎页</span>' : ($val['lcms'] == 0 ? [
                            "type"  => "link",
                            "title" => $appdef,
                            "icon"  => '<img src="/public/static/images/icons/config.svg"/>',
                            "url"   => "javascript:setDefapp('" . ssl_encode($val['id']) . "')",
                        ] : '<span style="color:#cccccc">同上级</span>'),
                    ]);
                    unset($val['pass'], $val['salt'], $val['parameter'], $val['level']);
                }
                TABLE::out($data);
                break;
            case 'list-save':
                if ($LF['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "禁止修改");
                }
                PUB::userSave(["id", "status"], [
                    "status" => $LC['value'] > 0 ? 1 : 0,
                ]);
                ajaxout(1, "修改成功", "reload");
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
                    ajaxout(0, "禁止停用自己");
                }
                $ids = implode(",", $ids);
                if (TABLE::del([
                    "table" => "admin",
                    "id"    => $ids,
                    "fake"  => "status:0",
                ])) {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理：停用用户/{$names}",
                    ]);
                    ajaxout(1, "停用成功", "reload");
                } else {
                    ajaxout(0, "停用失败");
                }
                break;
            case 'edit':
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => PUB::token2id($LF['token']),
                ]);
                $form['base'] = [
                    ["layui" => "upload", "title" => "头像",
                        "name"   => "LC[headimg]",
                        "value"  => $admin['headimg'],
                        "width"  => 200,
                        "height" => 200],
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
                            ["title" => "停用", "value" => 0],
                        ]],
                    ["layui" => "radio", "title" => "类型",
                        "name"   => "LC[cate]",
                        "value"  => $admin['cate'] ?: ($LF['cate'] ?: 0),
                        "radio"  => [
                            ["title" => "管理员", "value" => 0],
                            ["title" => "用户", "value" => 1],
                        ]],
                ];
                $form['level'] = [
                    ["layui" => "title", "title" => "权限设置"],
                    ["layui" => "des", "title" => "如“用户权限”中无可选择项，请先到“权限管理”里新建权限。<br>如需单独给某个用户自定义权限，可在添加用户后，点击用户列表中对应权限列，进行权限自定义。"],
                    ["layui"  => "selectN", "title" => "用户权限",
                        "name"    => "LC[type]",
                        "value"   => $admin['type'],
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
                if (count($_L['APP']['menu']['admin']['level'] ?: []) < 2) {
                    unset($form['base'][7]);
                    if (!$admin) {
                        $form['base'][] = [
                            "layui" => "input",
                            "name"  => "LC[cate]",
                            "type"  => "hidden",
                            "value" => $LF['cate'] ?: 0,
                        ];
                    }
                }
                require LCMS::template("own/admin/edit");
                break;
            case 'save':
                PUB::userSave(["id", "headimg", "name", "title", "pass", "email", "mobile", "type", "cate", "status", "lasttime", "addtime"]);
                ajaxout(1, "保存成功", "close");
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
                ajaxout(1, "保存成功", "close");
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
                        "name"    => "type",
                        "value"   => $admin['type'],
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
                        "type"  => $LF['type'],
                        "level" => $LC,
                    ];
                } else {
                    $LC = [
                        "type"  => $LF['type'],
                        "level" => [],
                    ];
                }
                PUB::userSave(["id", "type", "level"]);
                ajaxout(1, "保存成功", "close");
                break;
            default:
                $table = [
                    "url"     => "index&cate={$cate}&action=list",
                    "cols"    => [
                        ["checkbox" => "checkbox", "width" => 40],
                        ["title" => "ID", "field" => "id",
                            "width"  => 60,
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
                            "width"  => 180],
                        // ["title" => "上级用户", "field" => "lcms",
                        //     "width"  => 160],
                        ["title" => "到期时间", "field" => "lasttime",
                            "width"  => 170,
                            "align"  => "center"],
                        ["title"  => "存储空间", "field" => "storage",
                            "width"   => 150,
                            "issuper" => true,
                            "align"   => "center"],
                        ["title"  => "默认应用", "field" => "defapp",
                            "width"   => 120,
                            "issuper" => true,
                            "align"   => "center"],
                        ["title" => "状态", "field" => "statusT",
                            "width"  => 90,
                            "align"  => "center"],
                        ["title"  => "操作", "field" => "do",
                            "width"   => 100,
                            "align"   => "center",
                            "fixed"   => "right",
                            "toolbar" => [
                                ["title" => "编辑", "event" => "iframe",
                                    "url"    => "index&action=edit&token={token}",
                                    "color"  => "default"],
                                ["title" => "停用", "event" => "ajax",
                                    "url"    => "index&action=del",
                                    "color"  => "danger",
                                    "if"     => "d.status > 0",
                                    "tips"   => "停用用户不会删除用户信息，只是禁止登录和使用！"],
                            ]],
                    ],
                    "toolbar" => [
                        ["title" => $cate ? "添加用户" : "添加管理员", "event" => "iframe",
                            "url"    => "index&action=edit&cate={$cate}",
                            "color"  => "default"],
                        ["title" => "批量停用", "event" => "ajax",
                            "url"    => "index&action=del",
                            "color"  => "danger",
                            "tips"   => "停用用户不会删除用户信息，只是禁止登录和使用！"],
                    ],
                    "search"  => [
                        ["title" => "ID/账号/姓名/邮箱/手机", "name" => "name"],
                        ["title" => "用户状态", "name" => "status",
                            "type"   => "select",
                            "value"  => "",
                            "option" => [
                                ["title" => "启用", "value" => 1],
                                ["title" => "停用", "value" => 0],
                            ],
                        ],
                    ],
                ];
                $acount = sql_counter(["admin"]);
                if ($_L['developer']['lite'] === 1) {
                    unset($table['toolbar']);
                }
                require LCMS::template("own/admin/list");
                break;
        }
    }
    /**
     * @description: 普通用户列表
     * @return {*}
     */
    public function douser()
    {
        global $_L, $LF, $LC;
        $this->doindex(1);
    }
    /**
     * @description: 个人资料
     * @return {*}
     */
    public function doprofile()
    {
        global $_L, $LF, $LC;
        switch ($LF['action']) {
            case '2fa':
                if ($_L['LCMSADMIN']['2fa']) {
                    $_L['APP']['info'] = [];
                    LCMS::Y(200, "您已开启两步验证", [[
                        "title" => "立即关闭",
                        "url"   => "{$_L['url']['own_form']}profile&action=2fa-close",
                        "color" => "danger",
                    ]]);
                }
                LOAD::plugin("2FA/TOTP");
                $TOTP   = new TOTP();
                $secret = $TOTP->createSecret("uid:{$_L['LCMSADMIN']['id']}");
                $qrcode = "{$_L['url']['qrcode']}" . urlencode($TOTP->getQRCode("{$_L['LCMSADMIN']['name']}@" . parse_url($_L['url']['site'])['host'], $secret));
                $form   = [
                    ["layui" => "des", "title" => "▲ 开启两步验证后，在登录时会弹出验证窗口，输入APP中的6位两步验证码即可登录！"],
                    ["layui"      => "input", "title" => "验证码",
                        "name"        => "LC[code]",
                        "placeholder" => "请输入APP中生成的6位验证码",
                        "maxlength"   => 6,
                        "verify"      => "required"],
                    ["layui" => "btn", "title" => "立即开启"],
                ];
                require LCMS::template("own/admin/2fa");
                break;
            case '2fa-save':
                LOAD::plugin("2FA/TOTP");
                $TOTP = new TOTP();
                if (!$TOTP->verifyCode($LC['2fa'], $LC['code'])) {
                    ajaxout(0, "验证码错误");
                }
                PUB::userSave(["id", "2fa"], [
                    "id"  => $_L['LCMSADMIN']['id'],
                    "2fa" => $LC['2fa'],
                ]);
                ajaxout(1, "保存成功", "close");
                break;
            case '2fa-close':
                PUB::userSave(["id", "2fa"], [
                    "id"  => $_L['LCMSADMIN']['id'],
                    "2fa" => null,
                ]);
                $_L['APP']['info'] = [];
                LCMS::Y(200, "关闭成功");
                break;
            case 'verify':
                switch ($LF['by']) {
                    case 'email':
                        $form = [
                            ["layui" => "input", "title" => "邮箱", "name" => "LC[email]", "verify" => "required"],
                        ];
                        break;
                    case 'mobile':
                        $form = [
                            ["layui" => "input", "title" => "手机号", "name" => "LC[mobile]", "verify" => "required"],
                        ];
                        break;
                }
                $form = array_merge($form, [
                    ["layui" => "input", "title" => "by",
                        "name"   => "by",
                        "value"  => $LF['by'],
                        "type"   => "hidden"],
                    ["layui"    => "input", "title" => "验证码",
                        "name"      => "LC[code]",
                        "verify"    => "required",
                        "maxlength" => 6,
                        "cname"     => "user-admin-profile-code"],
                    ["layui" => "btn", "title" => "发送验证码"],
                ]);
                require LCMS::template("own/admin/verify");
                break;
            case 'verify-send':
                $uid = $_L['LCMSADMIN']['id'];
                LOAD::sys_class("userbase");
                switch ($LF['by']) {
                    case 'email':
                        USERBASE::isHave("email", $LC['email'], $uid);
                        USERBASE::sendCode([
                            "by"   => "email",
                            "to"   => $LC['email'],
                            "code" => $LC['code'],
                        ]);
                        break;
                    case 'mobile':
                        USERBASE::isHave("mobile", $LC['mobile'], $uid);
                        USERBASE::sendCode([
                            "by"   => "mobile",
                            "to"   => $LC['mobile'],
                            "code" => $LC['code'],
                        ]);
                        break;
                }
                ajaxout(2, "success", "openCode", [
                    "by" => $LF['by'],
                ]);
                break;
            case 'verify-save':
                LOAD::sys_class("userbase");
                $to = USERBASE::checkSendCode($LF['code']);
                PUB::userSave(["id", $LF['by']], [
                    "id"      => $_L['LCMSADMIN']['id'],
                    $LF['by'] => $to,
                ]);
                ajaxout(1, "保存成功");
                break;
            case 'save':
                PUB::userSave(["id", "headimg", "title", "pass"]);
                ajaxout(1, "保存成功", "close");
                break;
            default:
                $admin = LCMS::form([
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => $_L['LCMSADMIN']['id'],
                ]);
                $form['base'] = [
                    ["layui" => "upload", "title" => "头像",
                        "name"   => "LC[headimg]",
                        "value"  => $admin['headimg'],
                        "width"  => 200,
                        "height" => 200],
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
                        "nodrop" => true,
                        "cname"  => "user-admin-edit-htmlbox"],
                    ["layui" => "html", "title" => "手机",
                        "name"   => "mobile",
                        "value"  => $admin['mobile'] ?: "无",
                        "nodrop" => true,
                        "cname"  => "user-admin-edit-htmlbox"],
                ];
                LOAD::sys_class("email");
                if (EMAIL::init()['type']) {
                    $form['base'][4]['value'] = $admin['email'] ? "{$admin['email']}<a href=\"javascript:openVerify(`email`)\">修改邮箱</a>" : "无<a href=\"javascript:openVerify(`email`)\">立即绑定</a>";
                }
                LOAD::sys_class("sms");
                if (SMS::init()['type']) {
                    $form['base'][5]['value'] = $admin['mobile'] ? "{$admin['mobile']}<a href=\"javascript:openVerify(`mobile`)\">修改手机</a>" : "无<a href=\"javascript:openVerify(`mobile`)\">立即绑定</a>";
                }
                $LF['token'] = PUB::id2token($admin['id']);
                require LCMS::template("own/admin/edit");
                break;
        }
    }
    /**
     * @description: 用户切换
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
                $data = TABLE::set([
                    "table" => "admin",
                    "where" => $where,
                    "order" => "id ASC",
                    "bind"  => [
                        ":lasttime" => datenow(),
                        ":id"       => $LC['name'],
                        ":name"     => "%{$LC['name']}%",
                    ],
                ]);
                $adminlist = [];
                foreach ($data as $index => $val) {
                    unset($val['pass'], $val['salt'], $val['parameter']);
                    if (!$adminlist[$val['lcms']]) {
                        if ($val['lcms'] == 0) {
                            $adminlist[$val['lcms']] = sql_get([
                                "table" => "admin",
                                "where" => "type = 'lcms' AND lcms = 0",
                            ]);
                        } else {
                            $adminlist[$val['lcms']] = sql_get([
                                "table" => "admin",
                                "where" => "id = :id",
                                "bind"  => [
                                    ":id" => $val['lcms'],
                                ],
                            ]);
                        }
                    }
                    $admin = $adminlist[$val['lcms']];
                    $tname = "<span style=\"color:#E6A23C\">[ID:{$admin['id']}]-{$admin['title']}</span>";
                    if ($val['lcms'] == 0) {
                        if ($val['type'] == "lcms") {
                            $tname = "<span style=\"color:#b1b3b8\">无</span>";
                        }
                    }
                    $data[$index] = array_merge($val, [
                        "name"    => "{$val['name']}<span style=\"color:#b1b3b8\">-{$val['title']}</span>",
                        "lcms"    => $tname,
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
                $admin = sql_get([
                    "table" => "admin",
                    "where" => "id = {$LC['id']}",
                ]);
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
                        ["title"   => "账号-姓名", "field" => "name",
                            "minWidth" => 150],
                        ["title"   => "上级用户", "field" => "lcms",
                            "minWidth" => 130],
                        ["title" => "电话", "field" => "mobile",
                            "width"  => 120],
                        ["title" => "邮箱", "field" => "email",
                            "width"  => 120],
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
            case 'admin-level':
                $levels = sql_getall([
                    "table" => "admin_level",
                    "where" => "uid = :uid",
                    "order" => "id ASC",
                    "bind"  => [
                        ":uid" => $_L['ROOTID'] ?: $_L['LCMSADMIN']['id'],
                    ],
                ]);
                foreach ($levels as $level) {
                    $list[] = [
                        "value" => $level['id'],
                        "title" => "[ID:" . $level['id'] . "] {$level['name']}",
                    ];
                }
                echo json_encode($list ?: []);
                break;
        }
    }
}
