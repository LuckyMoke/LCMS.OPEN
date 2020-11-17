<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
@date_default_timezone_set('Asia/Shanghai');
define('IN_LCMS', true);
define('PATH_WEB', substr(dirname(__FILE__), 0, -13));
define('PATH_APP', PATH_WEB . "app/");
define('PATH_CACHE', PATH_WEB . "cache/");
define('PATH_PUBLIC', PATH_WEB . "public/");
define('PATH_UPLOAD', PATH_WEB . "upload/");
define('PATH_CORE', PATH_WEB . "core/");
define('PATH_CORE_CLASS', PATH_WEB . "core/class/");
define('PATH_CORE_FUNC', PATH_WEB . "core/function/");
define('PATH_CORE_PLUGIN', PATH_WEB . "core/plugin/");
define('PATH_APP_NOW', PATH_APP . L_TYPE . '/' . L_NAME . '/');
define('PATH_APP_OWN', PATH_APP . L_TYPE . '/' . L_NAME . '/' . L_MODULE . '/');
define('PHP_FILE', basename(__FILE__));
define('PHP_SELF', htmlentities($_SERVER['PHP_SELF']) == "" ? $_SERVER['SCRIPT_NAME'] : htmlentities($_SERVER['PHP_SELF']));
define('SYS_TIME', time());
define('HTTP_HOST', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
define('HTTP_PORT', $_SERVER["SERVER_PORT"]);
define('HTTP_TOP', @$_SERVER['HTTP_REFERER']);
define('HTTP_QUERY', $_SERVER['REQUEST_URI']);
define('SERVER_IP', $_SERVER['SERVER_ADDR']);
define('PAGE_START', microtime(true));
require_once PATH_CORE_FUNC . 'common.func.php';
require_once PATH_CORE_FUNC . 'file.func.php';
require_once 'mysql.class.php';
class install
{
    public static $configName = PATH_CORE . "config.php";
    public function __construct()
    {
        self::checkInstallLock();
    }
    /**
     * [checkInstallLock 检测是否已安装]
     * @return [type] [description]
     */
    private function checkInstallLock()
    {
        if (is_file('../core/install.lock')) {
            ajaxout(404, '<br><br><br><div class="center"><i class="layui-icon layui-icon-close-fill" style="font-size:140px;color:#ff6104;"></i><h2>对不起，该程序已经安装过了</h2><br/><p>如你要重新安装，请手动删除 /core/install.lock 文件</p></div>');
        }
    }
    /**
     * [checkDb 检查数据库连接]
     * @param  [type] $db [description]
     * @return [type]     [description]
     */
    private function checkDb($db)
    {
        DB::dbconn($db);
    }
    /**
     * [addConfig 添加数据库配置文件]
     * @param [type] $db [description]
     */
    private function addConfig($db)
    {
        $config = file_get_contents("data/config.php");
        foreach ($db as $key => $val) {
            $config = str_replace("[db_{$key}]", $val, $config);
        }
        file_put_contents(self::$configName, $config);
    }
    /**
     * [addLock 安装完成锁安装]
     */
    private function addLock()
    {
        file_put_contents(PATH_CORE . "install.lock", date("Y-m-d H:i:s"));
    }
    /**
     * [delInstall 安装完删除安装文件]
     * @return [type] [description]
     */
    private function delInstall()
    {
        deldir(PATH_WEB . "install");
    }
    /**
     * [checkDirs 检测文件夹权限]
     * @return [type] [description]
     */
    public function checkDirs()
    {
        $result = [
            [
                "name"  => "app",
                "desc"  => "应用下载安装目录",
                "power" => getdirpower(PATH_APP) ? 1 : 0,
            ],
            [
                "name"  => "cache",
                "desc"  => "框架所有缓存文件目录",
                "power" => getdirpower(PATH_CACHE) ? 1 : 0,
            ],
            [
                "name"  => "core",
                "desc"  => "框架所有核心文件目录",
                "power" => getdirpower(PATH_CORE) ? 1 : 0,
            ],
            [
                "name"  => "upload",
                "desc"  => "用户上传文件目录",
                "power" => getdirpower(PATH_UPLOAD) ? 1 : 0,
            ],
            [
                "name"  => "public",
                "desc"  => "框架所有公共资源",
                "power" => getdirpower(PATH_PUBLIC) ? 1 : 0,
            ],
        ];
        return $result;
    }
    /**
     * [installDb 安装数据库]
     * @param  [type] $db [description]
     * @return [type]     [description]
     */
    public function installDb($db)
    {
        $db['charset'] = "utf8mb4";
        self::checkDb($db);
        $data = DB::get_dbs();
        foreach ($data as $key => $val) {
            if ($val == $db['name']) {
                DB::query("DROP DATABASE {$db['name']}");
            }
        }
        DB::query("CREATE DATABASE {$db['name']} DEFAULT CHARACTER SET = utf8mb4 COLLATE utf8mb4_general_ci");
        DB::$link->select_db($db['name']);
        $mysql = file_get_contents('data/data.sql');
        $mysql = str_replace("[_PRE]", $db['pre'], $mysql);
        $mysql = str_replace("\r", "", $mysql);
        $mysql = explode(";\n", trim($mysql));
        foreach ($mysql as $key => $val) {
            DB::query("{$val};");
            if (DB::error()) {
                break;
                ajaxout(0, DB::error());
            };
        }
        self::addConfig($db);
    }
    /**
     * [addAdmin 添加管理员]
     * @param [type] $db [description]
     */
    public function addAdmin($admin)
    {
        require_once self::$configName;
        $db = $_L['mysql'];
        self::checkDb($db);
        DB::$link->select_db($db['name']);
        $query = "insert  into `{$db['pre']}admin`(`id`,`tuid`,`status`,`name`,`title`,`pass`,`email`,`mobile`,`type`,`balance`,`addtime`,`lasttime`,`logintime`,`parameter`,`ip`,`lcms`) values (1,0,1,'{$admin['name']}','超级管理员','{$admin['pass']}',NULL,NULL,'lcms','0.00','2019-01-01 00:00:00',NULL,NULL,'',NULL,0);";
        DB::query($query);
        self::addLock();
        if (!DB::error()) {
            self::delInstall();
        }
    }
}
