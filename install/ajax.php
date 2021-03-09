<?php
$action = @$_GET['action'];
require_once 'class/install.class.php';
$install = new install();
switch ($action) {
    case 'readme':
        ajaxout(1, "success", "", file_get_contents('data/readme.txt'));
        break;
    case 'dirs':
        $code   = 1;
        $dirs   = $install->checkDirs();
        $serv[] = [
            "name" => "系统信息",
            "desc" => php_uname('s'),
        ];
        $serv[] = [
            "name" => "环境信息",
            "desc" => $_SERVER["SERVER_SOFTWARE"],
        ];
        if (PHP_VERSION < "7.1.0") {
            $serv[] = [
                "name" => "PHP版本",
                "desc" => PHP_VERSION . " <span style='color:red'><i class='layui-icon layui-icon-close'></i>最低要求7.1.0</span>",
            ];
            $code = 0;
        } else {
            $serv[] = [
                "name" => "PHP版本",
                "desc" => PHP_VERSION . " <span style='color:green'><i class='layui-icon layui-icon-ok'></i>版本符合</span>",
            ];
        }
        if (extension_loaded("zip")) {
            $desc = "<span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>zip]</span>";
        } else {
            $desc = "<span style='color:red'>[<i class='layui-icon layui-icon-close'></i>zip]</span>";
            $code = 0;
        }
        if (extension_loaded("fileinfo")) {
            $desc .= " <span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>fileinfo]</span>";
        } else {
            $desc .= " <span style='color:red'>[<i class='layui-icon layui-icon-close'></i>fileinfo]</span>";
            $code = 0;
        }
        $serv[] = [
            "name" => "PHP扩展",
            "desc" => $desc,
        ];
        foreach ($result as $key => $val) {
            if ($val['power'] != 1) {
                $code = 0;
                break;
            }
        }
        ajaxout($code, "success", "", [
            "server" => $serv,
            "dirs"   => $dirs,
        ]);
        break;
    case 'db':
        $mysql = $install->installDb(@$_POST['db']);
        ajaxout(1, "success");
        break;
    case 'admin':
        $form = @$_POST['admin'];
        if ($form['pass'] != $form['repass']) {
            ajaxout(0, "两次密码不一样");
        }
        $form['pass'] = md5($form['pass']);
        $install->addAdmin($form);
        ajaxout(1, "success");
        break;
}
