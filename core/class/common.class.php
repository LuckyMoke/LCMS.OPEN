<?php
defined('IN_LCMS') or exit('No permission');
load::sys_func('file');
load::sys_class('mysql');
load::sys_class('session');
load::sys_class('http');
class common
{
    /**
     * [__construct 初始化]
     */
    public function __construct()
    {
        global $_L;
        $this->load_common_mysql();
        $this->load_common_form();
        $this->load_common_tables();
        $this->load_common_config();
        SESSION::start();
    }
    /**
     * [load_common_mysql 加载数据库]
     * @return [type] [description]
     */
    protected function load_common_mysql()
    {
        global $_L;
        if (is_file(PATH_CORE . 'config.php')) {
            require_once PATH_CORE . 'config.php';
            DB::dbconn($_L['mysql']);
        } else {
            okinfo("../install/");
            exit;
        }
    }
    /**
     * [load_common_form 表单过滤]
     * @return [type] [description]
     */
    protected function load_common_form()
    {
        global $_L;
        isset($_REQUEST['GLOBALS']) && exit('Access Error');
        parse_str(substr(strstr(HTTP_QUERY, '?'), 1), $QUERY);
        foreach ($QUERY as $k => $v) {
            $k[0] != '_' && $_L['form'][$k] = filterform($v);
        }
        foreach ($_COOKIE as $k => $v) {
            $k[0] != '_' && $_L['form'][$k] = filterform($v);
        }
        foreach ($_POST as $k => $v) {
            $k[0] != '_' && $_L['form'][$k] = filterform($v);
        }
        foreach ($_GET as $k => $v) {
            $k[0] != '_' && $_L['form'][$k] = filterform($v);
        }
    }
    /**
     * [load_common_tables 加载数据表]
     * @return [type] [description]
     */
    protected function load_common_tables()
    {
        global $_L;
        $table = DB::$mysql->get_tables();
        foreach ($table as $val) {
            $name          = str_replace($_L['mysql']['pre'], "", $val);
            $tables[$name] = $val;
        }
        $_L['table'] = $tables;
    }
    /**
     * [load_common_config 获取网站基本配置]
     * @return [type] [description]
     */
    protected function load_common_config()
    {
        global $_L;
        $_L['config']['admin'] = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ]);
        $_L['config']['web'] = LCMS::config([
            "name" => "config",
            "type" => "sys",
            "cate" => "web",
            "lcms" => true,
        ]);
        if ($_L['config']['admin']['development']) {
            $_L['config']['ver'] = "9999." . time();
            ob_start();
        } else {
            $version = PATH_CORE . "version";
            if (is_file($version)) {
                $_L['config']['ver'] = file_get_contents($version);
            }
        }
    }
    public function __destruct()
    {
        global $_L;
        if (!empty($_L['table'])) {
            DB::$mysql->close();
        }
        ob_end_flush();
        exit;
    }
}
load::sys_class('developer');
