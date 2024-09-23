<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2022-07-11 10:46:13
 * @LastEditTime: 2024-09-21 22:22:42
 * @Description: 权限管理
 * Copyright 2022 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
load::own_class('pub');
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
                $where = LCMS::SUPER() ? "" : "uid = :uid";
                $data  = TABLE::set([
                    "table"  => "admin_level",
                    "where"  => $where,
                    "order"  => "id DESC",
                    "fields" => "id, name, uid",
                    "bind"   => [
                        ":uid" => $_L['LCMSADMIN']['id'],
                    ],

                ]);
                $adminlist = [];
                foreach ($data as $index => $val) {
                    if (!$adminlist[$val['uid']]) {
                        $adminlist[$val['uid']] = sql_get([
                            "admin", "id = '{$val['uid']}'",
                        ]);
                    }
                    $admin        = $adminlist[$val['uid']];
                    $data[$index] = array_merge($val, [
                        "token" => PUB::id2token($val['id']),
                        "uid"   => $admin['title'] . " - [" . $admin['name'] . "]",
                    ]);
                }
                TABLE::out($data);
                break;
            case 'del':
                $LC['id'] = PUB::token2id($LC['token']);
                if (sql_counter([
                    "table" => "admin",
                    "where" => "type = :type",
                    "bind"  => [
                        ":type" => $LC['id'],
                    ],
                ]) > 0) {
                    ajaxout(0, "有用户使用此权限");
                }
                if (TABLE::del("admin_level")) {
                    LCMS::log([
                        "type" => "system",
                        "info" => "用户管理-删除权限-{$LC['name']}",
                    ]);
                    ajaxout(1, "删除成功", "reload");
                } else {
                    ajaxout(0, "删除失败");
                }
                break;
            case 'edit':
                list($level, $hide) = PUB::getLevelList(LCMS::form([
                    "do"    => "get",
                    "table" => "admin_level",
                    "id"    => $LF['id'],
                ]));
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
                require LCMS::template("own/power/edit");
                break;
            case 'save':
                $LC['id'] = PUB::token2id($LF['token']);
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
            case 'copy':
                $where = LCMS::SUPER() ? "" : " AND uid = :uid";
                $level = sql_get([
                    "table" => "admin_level",
                    "where" => "id = :id{$where}",
                    "bind"  => [
                        ":id"  => $LC['id'],
                        ":uid" => $_L['LCMSADMIN']['id'],
                    ],
                ]);
                if ($level) {
                    unset($level['id']);
                    sql_insert([
                        "table" => "admin_level",
                        "data"  => array_merge($level, [
                            "name" => "{$level['name']}-副本",
                        ]),
                    ]);
                    ajaxout(1, "复制成功", "reload");
                }
                ajaxout(0, "复制失败");
                break;
            default:
                $table = [
                    "url"     => "index&action=list",
                    "cols"    => [
                        ["title" => "ID", "field" => "id",
                            "width"  => 80,
                            "align"  => "center"],
                        ["title" => "权限名", "field" => "name",
                            "width"  => 200],
                        ["title" => "添加人", "field" => "uid",
                            "width"  => 300],
                        ["title"   => "操作", "field" => "do",
                            "minWidth" => 130,
                            "fixed"    => "right",
                            "toolbar"  => [
                                ["title" => "编辑", "event" => "iframe",
                                    "url"    => "index&action=edit&token={token}",
                                    "color"  => "default"],
                                ["title" => "复制", "event" => "ajax",
                                    "url"    => "index&action=copy",
                                    "color"  => "warm",
                                    "tips"   => "确认复制此权限？"],
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
                if (LCMS::SUPER() && $_L['developer']['lite'] === 1) {
                    unset($table['cols'][3]['toolbar'][1], $table['cols'][3]['toolbar'][2], $table['toolbar']);
                }
                require LCMS::template("own/power/list");
                break;
        }
    }
}
