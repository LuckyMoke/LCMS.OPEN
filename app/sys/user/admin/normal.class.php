<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
load::sys_class('userbase');
load::sys_class('table');
class normal extends adminbase
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
            "url"     => $_L['url']['own_form'] . "ajax&action=normal-list",
            "cols"    => array(
                array("checkbox" => "checkbox", "width" => 80),
                array("title" => "ID", "field" => "id", "width" => 80, "align" => "center"),
                array("title" => "帐号", "field" => "name", "minWidth" => 200),
                array("title" => "用户名", "field" => "title", "edit" => "text", "minWidth" => 200),
                array("title" => "邮箱", "field" => "email"),
                array("title" => "手机号", "field" => "mobile", "minWidth" => 120),
                array("title" => "注册时间", "field" => "addtime", "minWidth" => 180),
                array("title" => "账号状态", "field" => "status", "width" => 100, "align" => "center"),
                array("title" => "操作", "field" => "do", "width" => 120, "align" => "center", "toolbar" => array(
                    array("title" => "编辑", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=normal-edit", "color" => "default"),
                    array("title" => "删除", "event" => "ajax", "url" => $_L['url']['own_form'] . "ajax&action=normal-list-del", "color" => "danger", "tips" => "确认删除？"),
                )),
            ),
            "toolbar" => array(
                array("title" => "添加用户", "event" => "iframe", "url" => $_L['url']['own_form'] . "iframe&action=normal-edit", "color" => "default"),
                array("title" => "批量删除", "event" => "ajax", "url" => $_L['url']['own_form'] . "ajax&action=normal-list-del", "color" => "danger", "tips" => "确认删除？"),
            ),
            "search"  => array(
                array("title" => "用户名/邮箱/手机号", "name" => "name"),
            ),
        );
        require LCMS::template("own/normal-list");
    }
    public function doajax()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'normal-list':
                $where = $form['name'] ? " AND (name LIKE '%{$form[name]}%' OR title LIKE '%{$form[name]}%' OR eamil LIKE '%{$form[name]}%' OR moblie LIKE '%{$form[name]}%')" : "";
                $data  = table::data("user", "lcms = '{$_L['ROOTID']}'" . $where, "id DESC");
                foreach ($data['data'] as $key => $value) {
                    $checked                      = $data['data'][$key]['status'] ? "checked" : "";
                    $data['data'][$key]['status'] = "<input type='checkbox' data-url='{$_L['url']['own_form']}ajax&action=normal-list-status&id={$data['data'][$key]['id']}' lay-skin='switch' lay-text='启用|禁用' {$checked}>";
                }
                table::out($data);
                break;
            case 'normal-list-save':
                sql_update(["user", [
                    $form['name'] => $form['value'],
                ], "id = '{$form[id]}'"]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'normal-list-status':
                sql_update(["user", [
                    "status" => $form['value'] ? "1" : "0",
                ], "id = '{$_L['form']['id']}'"]);
                if (sql_error()) {
                    ajaxout(0, "保存失败");
                } else {
                    ajaxout(1, "保存成功");
                }
                break;
            case 'normal-list-del':
                if (table::del("user")) {
                    ajaxout(1, "删除成功");
                } else {
                    ajaxout(0, "删除失败");
                }
        }
    }
    public function doiframe()
    {
        global $_L;
        $form = $_L['form']['LC'];
        switch ($_L['form']['action']) {
            case 'normal-edit':
                $normal = userbase::get("id", $_L['form']['id']);
                $form   = array(
                    array("layui" => "input", "title" => "账号", "name" => "LC[name]", "value" => $normal['name'], "tips" => "账号用于登陆不能重复、不能修改", "verify" => "required|name", "disabled" => $normal['name'] ? "1" : ""),
                    array("layui" => "input", "title" => "用户名", "name" => "LC[title]", "value" => $normal['title'], "tips" => "用户名用于显示", "verify" => "required"),
                    array("layui" => "input", "type" => "password", "title" => "密码", "name" => "LC[pass]", "value" => $normal['pass'], "placeholder" => "请输入用户密码", "verify" => "required"),
                    array("layui" => "input", "type" => "password", "title" => "重复密码", "name" => "repass", "value" => $normal['pass'], "placeholder" => "请再次输入用户密码", "verify" => "required|pass"),
                    array("layui" => "input", "type" => "email", "title" => "邮箱", "name" => "LC[email]", "value" => $normal['email']),
                    array("layui" => "input", "type" => "mobile", "title" => "手机号", "name" => "LC[mobile]", "value" => $normal['mobile']),
                    array("layui" => "on", "title" => "账号状态", "name" => "LC[status]", "value" => $normal['status'], "text" => "启用|禁用"),
                    array("layui" => "btn", "fluid" => true, "title" => "立即保存"),
                );
                require LCMS::template("own/iframe/normal-edit");
                break;
            case 'normal-register':
                $user = userbase::register($form);
                if ($user['code'] == "0") {
                    ajaxout(0, $user['msg']);
                } else {
                    ajaxout(1, "保存成功", "close");
                }
                break;
            case 'normal-save':
                if ($_L['form']['oldpass'] != $form['pass']) {
                    $_L['form']['LC']['pass'] = md5($form['pass']);
                }
                $user = userbase::update($form);
                if ($user['code'] == "0") {
                    ajaxout(0, $user['msg']);
                } else {
                    ajaxout(1, "保存成功", "close");
                }
                break;
        }
    }
}
