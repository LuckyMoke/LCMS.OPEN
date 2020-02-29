<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('table');
class admin extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $table = array(
            "url"     => $_L['url']['own_form'] . "ajax&action=admin-list",
            "cols"    => array(
                array("checkbox" => "checkbox", "width" => 80),
                array("title" => "ID", "field" => "id", "width" => 80, "align" => "center"),
                array("title" => "帐号", "field" => "name", "minWidth" => 100),
                array("title" => "用户名", "field" => "title", "edit" => "text", "minWidth" => 150),
                array("title" => "账户余额", "field" => "balance", "width" => 100, "edit" => "text"),
                array("title" => "用户权限", "field" => "type"),
                array("title" => "上级用户", "field" => "lcms", "minWidth" => 150),
                array("title" => "最后登录时间", "field" => "logintime", "minWidth" => 180),
                array("title" => "最后登录IP", "field" => "ip", "width" => 150),
                array("title" => "到期时间", "field" => "lasttime", "minWidth" => 180),
                array("title" => "账号状态", "field" => "status", "width" => 100, "align" => "center"),
                array("title" => "操作", "field" => "do", "width" => 120, "align" => "center", "toolbar" => array(
                    array("title" => "编辑", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=admin-edit", "color" => "default"),
                    array("title" => "删除", "event" => "ajax", "url" => $_L['url']['own_form'] . "ajax&action=admin-list-del", "color" => "danger", "tips" => "确认删除？"),
                )),
            ),
            "toolbar" => array(
                array("title" => "添加管理员", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=admin-edit", "color" => "default"),
                array("title" => "批量删除", "event" => "ajax", "url" => $_L['url']['own_form'] . "ajax&action=admin-list-del", "color" => "danger", "tips" => "确认删除？"),
            ),
            "search"  => array(
                array("title" => "请输入用户名查找", "name" => "name"),
            ),
        );
        require LCMS::template("own/admin-list");
    }
    public function dolevel()
    {
        global $_L;
        $table = array(
            "url"     => $_L['url']['own_form'] . "ajax&action=admin-level-list",
            "cols"    => array(
                array("title" => "ID", "field" => "id", "width" => 80, "align" => "center"),
                array("title" => "权限名", "field" => "name", "edit" => "text"),
                array("title" => "添加人", "field" => "uid"),
                array("title" => "操作", "field" => "do", "width" => 120, "align" => "center", "toolbar" => array(
                    array("title" => "编辑", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=admin-level-edit", "color" => "default"),
                    array("title" => "删除", "event" => "ajax", "url" => $_L['url']['own_form'] . "ajax&action=admin-level-list-del", "color" => "danger", "tips" => "确认删除？"),
                )),
            ),
            "toolbar" => array(
                array("title" => "添加权限", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=admin-level-edit", "color" => "default"),
            ),
        );
        require LCMS::template("own/admin-list");
    }
    public function doajax()
    {
        global $_L;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "没有权限，禁止访问");
        }
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'admin-list':
                $where     = $form['name'] ? " AND (name LIKE '%{$form[name]}%' OR title LIKE '%{$form[name]}%')" : "";
                $data      = LCMS::SUPER() ? table::data("admin", "id != 0" . $where, "id ASC") : table::data("admin", "(lcms = '{$_L[LCMSADMIN][id]}' OR id = '{$_L[LCMSADMIN][id]}')" . $where, "id ASC");
                $adminlist = sql_getall(["admin"]);
                $levellist = sql_getall(["admin_level"]);
                foreach ($data['data'] as $key => $val) {
                    $data['data'][$key]['type']   = $data['data'][$key]['type'] == "lcms" ? "超级权限" : $data['data'][$key]['type'];
                    $checked                      = $data['data'][$key]['status'] ? "checked" : "";
                    $data['data'][$key]['status'] = "<input type='checkbox' data-url='{$_L['url']['own_form']}ajax&action=admin-list-status&id={$data['data'][$key]['id']}' lay-skin='switch' lay-text='启用|禁用' {$checked}>";
                    foreach ($adminlist as $info) {
                        if ($info['id'] == $val['lcms']) {
                            $data['data'][$key]['lcms'] = $info['title'] . " - [" . $info['name'] . "]";
                        } elseif ($val['lcms'] == "0") {
                            $data['data'][$key]['lcms'] = "超级管理员";
                        }
                        continue;
                    }
                    foreach ($levellist as $info) {
                        if ($info['id'] == $val['type']) {
                            $data['data'][$key]['type'] = $info['name'] . " - [ID" . $info['id'] . "]";
                        } elseif ($val['type'] == "lcms") {
                        }
                        continue;
                    }
                }
                table::out($data);
                break;
            case 'admin-list-save':
                sql_update(["admin", [
                    $form['name'] => $form['value'],
                ], "id = '{$form[id]}'"]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'admin-list-del':
                if ($form['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "自己不能删除自己");
                    exit;
                } elseif ($form[0]['id']) {
                    foreach ($form as $key => $val) {
                        if ($val['id'] == $_L['LCMSADMIN']['id']) {
                            ajaxout(0, "自己不能删除自己");
                            exit;
                        }
                    }
                }
                if (table::del("admin")) {
                    ajaxout(1, "删除成功");
                } else {
                    ajaxout(0, "删除失败");
                }
                break;
            case 'admin-list-status':
                if ($_L['form']['id'] == $_L['LCMSADMIN']['id']) {
                    ajaxout(0, "自己不能设置自己");
                    exit;
                }
                sql_update(["admin", [
                    "status" => $form['value'] ? "1" : "0",
                ], "id = '{$_L['form']['id']}'"]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'admin-level-list':
                if (LCMS::SUPER()) {
                    $data = table::data("admin_level", "", "id ASC");
                } else {
                    $data = table::data("admin_level", "uid = '{$_L[LCMSADMIN][id]}'", "id ASC");
                }
                $adminlist = sql_getall(["admin"]);
                foreach ($data['data'] as $key => $val) {
                    foreach ($adminlist as $info) {
                        if ($info['id'] == $val['uid']) {
                            $data['data'][$key]['uid'] = $info['title'] . " - [" . $info['name'] . "]";
                            continue;
                        }
                    }
                }
                table::out($data);
                break;
            case 'admin-level-list-save':
                sql_update(["admin_level", [
                    $form['name'] => $form['value'],
                ], "id = '{$form[id]}'"]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'admin-level-list-del':
                $adminlist = sql_getall(["admin", "type = '{$form[id]}'"]);
                if ($adminlist) {
                    ajaxout(0, "有管理员使用此权限");
                } else {
                    if (table::del("admin_level")) {
                        ajaxout(1, "删除成功");
                    } else {
                        ajaxout(0, "删除失败");
                    }
                }
                break;
        }
    }
    public function doiframe()
    {
        global $_L;
        if ($_L['LCMSADMIN']['lcms'] != "0") {
            LCMS::X(403, "没有权限，禁止访问");
        }
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'admin-edit':
                $admin = $_L['form']['id'] ? LCMS::form(array(
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => $_L['form']['id'],
                )) : array();
                $adminall = LCMS::SUPER() ? sql_getall(["admin", "lcms = '0' AND lasttime > now() AND type != 'lcms'", "id ASC"]) : sql_getall(["admin", "lasttime > now() AND lcms = '{$_L[LCMSADMIN][id]}'", "id ASC"]);
                $levelall = sql_getall(["admin_level", "uid = '{$_L[LCMSADMIN][id]}' OR id = '{$admin[type]}'"]);
                if (LCMS::SUPER()) {
                    $adminlist[] = array(
                        "value" => "0",
                        "title" => "超级管理员",
                    );
                }
                foreach ($adminall as $key => $val) {
                    if ($val['id'] != $_L['form']['id']) {
                        $adminlist[] = array(
                            "value" => $val['id'],
                            "title" => $val['title'] . "[" . $val['name'] . "]",
                        );
                    }
                }
                foreach ($levelall as $key => $val) {
                    $levellist[] = array(
                        "value" => $val['id'],
                        "title" => $val['name'] . " - [ID" . $val['id'] . "]",
                    );
                }
                require LCMS::template("own/iframe/admin-edit");
                break;
            case 'admin-check-name':
                $admin = sql_get(["admin", "name = '{$_L[form][name]}'"]);
                if ($admin) {
                    ajaxout(0, "用户名重复！");
                };
                break;
            case 'admin-save':
                if ($form['oldpass'] != $form['pass']) {
                    $_L['form']['LC']['pass'] = md5($form['pass']);
                }
                if ($form['id']) {
                    if (!LCMS::SUPER() && $form['id'] == $_L['LCMSADMIN']['id']) {
                        unset($_L['form']['LC']['name']);
                    }
                }
                unset($_L['form']['LC']['oldpass']);
                LCMS::form(array(
                    "table" => "admin",
                ));
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    ajaxout(1, "保存成功", "close");
                }
                break;
            case 'admin-level-edit':
                $level = $_L['form']['id'] ? LCMS::form(array(
                    "do"    => "get",
                    "table" => "admin_level",
                    "id"    => $_L['form']['id'],
                )) : array();
                ksort($level['sys']);
                ksort($level['open']);
                $appall = level::appall();
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
                                            $html .= "<input type='hidden' name='LC[{$type}][{$name}][{$class}][{$key}]' value='0'>";
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
                            }
                        }
                    }
                }
                require LCMS::template("own/iframe/admin-level-edit");
                break;
            case 'admin-level-save':
                LCMS::form(array(
                    "table" => "admin_level",
                    "unset" => true,
                ));
                if (sql_error()) {
                    ajaxout(0, sql_error());
                } else {
                    ajaxout(1, "保存成功", "close");
                }
                break;
        }
    }
    public function doprofile()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'admin-edit':
                $admin = LCMS::form(array(
                    "do"    => "get",
                    "table" => "admin",
                    "id"    => $_L['LCMSADMIN']['id'],
                ));
                require LCMS::template("own/iframe/admin-edit");
                break;
            case 'admin-save':
                if ($form['oldpass'] != $form['pass']) {
                    $_L['form']['LC']['pass'] = md5($form['pass']);
                }
                if ($form['id']) {
                    if (!LCMS::SUPER() && $form['id'] == $_L['LCMSADMIN']['id']) {
                        unset($_L['form']['LC']['name']);
                    }
                }
                unset($_L['form']['LC']['oldpass']);
                $_SESSION['LCMSADMIN']['title'] = $_L['form']['LC']['title'];
                LCMS::form(array(
                    "table" => "admin",
                ));
                if (sql_error()) {
                    ajaxout(0, "保存失败", "", sql_error());
                } else {
                    ajaxout(1, "保存成功", "close");
                }
                break;
        }
    }
    public function doconfig()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                LCMS::config(array(
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                ajaxout(1, "保存成功");
                break;
            default:
                $config = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "admin",
                    "lcms" => true,
                ));
                $form = array(
                    array("layui" => "on", "title" => "管理员注册", "name" => "LC[reg]", "value" => $config['reg'], "text" => "开启|关闭"),
                    array("layui" => "input", "title" => "默认权限ID", "name" => "LC[default_level]", "value" => $config['default_level'], "verify" => "required"),
                    array("layui" => "btn", "title" => "立即保存"),
                );
                require LCMS::template("own/admin-config");
                break;
        }
    }
}
