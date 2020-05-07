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
        foreach ($_COOKIE as $_key => $_value) {
            $_key{0} != '_' && $_L['form'][$_key] = filterform($_value);
        }
        foreach ($_POST as $_key => $_value) {
            $_key{0} != '_' && $_L['form'][$_key] = filterform($_value);
        }
        foreach ($_GET as $_key => $_value) {
            $_key{0} != '_' && $_L['form'][$_key] = filterform($_value);
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
        $_L['config']['admin'] = LCMS::config(array(
            "name" => "config",
            "type" => "sys",
            "cate" => "admin",
            "lcms" => true,
        ));
        $_L['config']['web'] = LCMS::config(array(
            "name" => "config",
            "type" => "sys",
            "cate" => "web",
            "lcms" => true,
        ));
        $_L['config']['admin']['ver'] = $_L['config']['admin']['ver'] == "{time}" ? time() : $_L['config']['admin']['ver'];
    }
    public function __destruct()
    {
        global $_L;
        if (!empty($_L['table'])) {
            DB::$mysql->close();
        }
        exit;
    }
}
