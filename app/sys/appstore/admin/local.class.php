<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-10-25 14:46:26
 * @Description:本地应用列表
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class local extends adminbase
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
            case 'data':
                $power = false;
                $super = LCMS::SUPER();
                if ($super || $_L['LCMSADMIN']['id'] == $_L['ROOTID']) {
                    $power = true;
                }
                if ($power && $_L['developer']['lite'] !== 1) {
                    $tips = "拖动左上角可排序，点击应用LOGO可直接打开应用。";
                    if ($super) {
                        $tips .= "<code>修复:</code>应用数据表有问题可点击修复。<code>卸载:</code>卸载应用会删除应用文件和应用所有数据。";
                    }
                }
                ajaxout(1, "success", "", [
                    "tips"     => $tips,
                    "power"    => $power,
                    "super"    => $super,
                    "appstore" => $_L['developer']['appstore'] !== 0 ? true : false,
                    "lite"     => $_L['developer']['lite'] !== 1 ? false : true,
                    "apps"     => LEVEL::applist("open", true),
                ]);
                break;
            default:
                require LCMS::template("own/local/index");
                break;
        }
    }
    /**
     * @description: 保存应用排序
     * @param {*}
     * @return {*}
     */
    public function dosaveindex()
    {
        global $_L, $LF, $LC;
        LCMS::config([
            "do"    => "save",
            "name"  => "menu",
            "type"  => "sys",
            "cate"  => "admin",
            "form"  => [
                "open" => $LC,
            ],
            "unset" => "sys|open",
        ]);
        if (sql_error()) {
            ajaxout(0, [
                "title" => "保存失败",
                "msg"   => sql_error(),
            ]);
        } else {
            ajaxout(1, "保存成功");
        }
    }
    /**
     * @description: 设置默认应用
     * @param {*}
     * @return {*}
     */
    public function dosetdefault()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() && LCMS::X(403, "请在子账号下设置<br/>需要给子账号添加“本地应用”权限");
        switch ($LF['action']) {
            case 'save':
                LCMS::config([
                    "do"   => "save",
                    "name" => "menu",
                    "type" => "sys",
                    "cate" => "admin",
                    "form" => [
                        "default" => $LC,
                    ],
                ]);
                ajaxout(1, "保存成功", "close");
                break;
            default:
                $open = LEVEL::applist("open", true);
                $open || LCMS::X(404, "您未安装任何应用");
                $config = LCMS::config([
                    "name" => "menu",
                    "type" => "sys",
                    "cate" => "admin",
                ]);
                foreach ($open as $name => $app) {
                    $applist[] = [
                        "title" => $app['title'],
                        "value" => $name,
                    ];
                }
                $form = [
                    ["layui" => "radio", "title" => "功能状态",
                        "name"   => "LC[on]",
                        "value"  => $config['default']['on'] ?: 0,
                        "radio"  => [
                            ["title" => "关闭", "value" => 0, "tab" => "tab0"],
                            ["title" => "打开第一个应用", "value" => 1, "tab" => "tab1"],
                            ["title" => "打开指定应用", "value" => 2, "tab" => "tab2"],
                        ]],
                    ["layui" => "select", "title" => "指定应用",
                        "name"   => "LC[name]",
                        "value"  => $config['default']['name'],
                        "cname"  => "hidden tab2",
                        "option" => $applist],
                    ["layui" => "btn", "title" => "立即保存"],
                ];
                require LCMS::template("own/local/default");
                break;
        }
    }
    /**
     * @description: 卸载应用
     * @param {*}
     * @return {*}
     */
    public function douninstall()
    {
        global $_L, $LF, $LC;
        LCMS::SUPER() || ajaxout(0, '无操作权限');
        $app = $LF['app'];
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $app)) {
            ajaxout(0, '未找到应用信息');
        }
        $dir = PATH_APP . "open/{$app}/";
        if (is_file($dir . "uninstall.sql")) {
            $this->updatesql(str_replace("[TABLE_PRE]", $_L['mysql']['pre'], file_get_contents($dir . "uninstall.sql")));
        }
        deldir($dir);
        LCMS::log([
            "type" => "system",
            "info" => "卸载应用-{$app}",
        ]);
        ajaxout(1, '卸载成功');
    }
    /**
     * @description: 更新数据库
     * @param string $sqldata
     * @return {*}
     */
    private function updatesql($sqldata)
    {
        global $_L;
        $sqldata = str_replace("\r", "", $sqldata);
        $sqldata = explode(";\n", trim($sqldata));
        foreach ($sqldata as $val) {
            if ($val) {
                sql_query($val);
            }
        }
        return true;
    }
}
