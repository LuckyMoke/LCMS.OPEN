<?php
$action = @$_GET['action'];
require_once 'class/install.class.php';
$install = new install();
switch ($action) {
    case 'readme':
        ajaxout(1, "success", "", file_get_contents('data/readme.txt'));
        break;
    case 'dirs':
        $code = 1;
        $dirs = $install->checkDirs();
        $serv = [
            "os"  => php_uname('s'),
            "sys" => $_SERVER["SERVER_SOFTWARE"],
            "php" => PHP_VERSION,
        ];
        if ($serv['sys'] < "7.1.0") {
            $serv['php'] = "{$serv['php']} <span style='color:red'>最低要求7.1.0</span>";
            $code        = 0;
        } else {
            $serv['php'] = "{$serv['php']} <span style='color:green'><i class='layui-icon layui-icon-ok'></i>版本符合</span>";
        }
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
