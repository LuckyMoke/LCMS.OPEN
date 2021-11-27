<?php
$action = @$_GET['action'];
require_once 'class/install.class.php';
$install = new install();
switch ($action) {
    case 'readme':
        if ($_SERVER['PHP_SELF'] != "/install/ajax.php") {
            ajaxout(404, "<h1 style='text-align:center;padding:50px 0;'>本程序无法在二级目录下安装</h1>");
        }
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
        if (PHP_VERSION < "7.2.0") {
            $serv[] = [
                "name" => "PHP版本",
                "desc" => PHP_VERSION . " <span style='color:red'><i class='layui-icon layui-icon-close'></i>最低要求7.2.0</span>",
            ];
            $code = 0;
        } elseif (PHP_VERSION < "8.0.0") {
            $serv[] = [
                "name" => "PHP版本",
                "desc" => PHP_VERSION . " <span style='color:green'><i class='layui-icon layui-icon-ok'></i>版本可用 / 推荐 PHP8.0 及其以上版本</span>",
            ];
        } else {
            $serv[] = [
                "name" => "PHP版本",
                "desc" => PHP_VERSION . " <span style='color:green'><i class='layui-icon layui-icon-ok'></i>版本可用</span>",
            ];
        }
        if (extension_loaded("zip")) {
            $desc = "<span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>zip]</span>";
        } else {
            $desc = "<a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AFzip%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:red'>[<i class='layui-icon layui-icon-close'></i>zip]</a>";
            $code = 0;
        }
        if (class_exists('pdo')) {
            $desc .= " <span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>pdo]</span>";
        } else {
            $desc .= " <a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AFpdo%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:red'>[<i class='layui-icon layui-icon-close'></i>pdo]</a>";
            $code = 0;
        }
        if (extension_loaded("mysqli")) {
            $desc .= " <span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>mysqli]</span>";
        } else {
            $desc .= " <a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AFmysqli%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:red'>[<i class='layui-icon layui-icon-close'></i>mysqli]</a>";
            $code = 0;
        }
        if (extension_loaded("pdo_mysql")) {
            $desc .= " <span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>pdo_mysql]</span>";
        } else {
            $desc .= " <a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AFpdo_mysql%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:red'>[<i class='layui-icon layui-icon-close'></i>pdo_mysql]</a>";
            $code = 0;
        }
        if (extension_loaded("pdo_sqlite")) {
            $desc .= " <span style='color:green'>[<i class='layui-icon layui-icon-ok'></i>pdo_sqlite]</span>";
        } else {
            $desc .= " <a href='https://www.baidu.com/s?wd=PHP%E5%BC%80%E5%90%AFpdo_sqlite%E6%89%A9%E5%B1%95&ie=UTF-8' target='_blank' style='color:red'>[<i class='layui-icon layui-icon-close'></i>pdo_sqlite]</a>";
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
