<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
date_default_timezone_set("Asia/Shanghai");
if (version_compare(PHP_VERSION, "7.2", "lt")) {
    echo "当前PHP版本" . PHP_VERSION . "，版本过低，请使用7.2及以上版本，推荐使用8.0及以上版本！";
    exit;
}
if (!preg_match("/^\w+$/", L_TYPE . L_NAME . L_MODULE . L_CLASS . L_ACTION)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
define("IN_LCMS", true);
define("PATH_WEB", substr(dirname(__FILE__), 0, -4));
define("PATH_APP", PATH_WEB . "app/");
define("PATH_CACHE", PATH_WEB . "cache/");
define("PATH_PUBLIC", PATH_WEB . "public/");
define("PATH_UPLOAD", PATH_WEB . "upload/");
define("PATH_CORE", PATH_WEB . "core/");
define("PATH_CORE_CLASS", PATH_WEB . "core/class/");
define("PATH_CORE_FUNC", PATH_WEB . "core/function/");
define("PATH_CORE_PLUGIN", PATH_WEB . "core/plugin/");
define("PATH_APP_NOW", PATH_APP . L_TYPE . "/" . L_NAME . "/");
define("PATH_APP_OWN", PATH_APP . L_TYPE . "/" . L_NAME . "/" . L_MODULE . "/");
define("PHP_FILE", basename(__FILE__));
define("PHP_SELF", htmlentities($_SERVER['PHP_SELF']) == "" ? $_SERVER['SCRIPT_NAME'] : htmlentities($_SERVER['PHP_SELF']));
define("SYS_TIME", time());
define("HTTP_HOST", isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
define("HTTP_PORT", $_SERVER['SERVER_PORT']);
define("HTTP_TOP", $_SERVER['HTTP_REFERER']);
define("HTTP_URI", $_SERVER['REQUEST_URI']);
define("SERVER_IP", $_SERVER['SERVER_ADDR']);
define("PAGE_START", microtime(true));
require_once PATH_CORE_FUNC . "common.func.php";
require_once PATH_CORE_CLASS . "lcms.class.php";
require_once PATH_CORE_CLASS . "load.class.php";
define("CLIENT_IP", LCMS::IP());
LOAD::init();
