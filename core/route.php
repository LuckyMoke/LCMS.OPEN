<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
date_default_timezone_set("Asia/Shanghai");
if (PHP_VERSION_ID < 70300) {
    http_response_code(500);
    echo "当前PHP版本" . PHP_VERSION . "，版本过低，请使用7.3及以上版本，推荐使用8.1及以上版本！";
    exit;
}
foreach ([
    "L_TYPE", "L_NAME", "L_MODULE", "L_CLASS", "L_ACTION",
] as $_k) {
    if (
        !defined($_k) ||
        !preg_match("/^\w+$/D", (string) constant($_k))
    ) {
        http_response_code(403);
        exit;
    }
}
unset($_k);
define("IN_LCMS", true);
define("PATH_WEB", dirname(__DIR__) . "/");
define("PATH_APP", PATH_WEB . "app/");
define("PATH_CACHE", PATH_WEB . "cache/");
define("PATH_PUBLIC", PATH_WEB . "public/");
define("PATH_UPLOAD", PATH_WEB . "upload/");
define("PATH_CORE", PATH_WEB . "core/");
define("PATH_CORE_CLASS", PATH_WEB . "core/class/");
define("PATH_CORE_PQFILE", PATH_WEB . "core/pqfile/");
define("PATH_CORE_FUNC", PATH_WEB . "core/function/");
define("PATH_CORE_PLUGIN", PATH_WEB . "core/plugin/");
define("PATH_APP_NOW", PATH_APP . L_TYPE . "/" . L_NAME . "/");
define("PATH_APP_OWN", PATH_APP_NOW . L_MODULE . "/");
define("PHP_FILE", basename(__FILE__));
$_self = $_SERVER['PHP_SELF'] ?? ($_SERVER['SCRIPT_NAME'] ?? '');
define("PHP_SELF", htmlspecialchars($_self, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"));
unset($_self);
define("SYS_TIME", time());
define("HTTP_HOST", $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ""));
define("HTTP_PORT", $_SERVER['SERVER_PORT'] ?? 80);
define("HTTP_TOP", $_SERVER['HTTP_REFERER'] ?? "");
define("SERVER_IP", $_SERVER['SERVER_ADDR'] ?? "127.0.0.1");
define("PAGE_START", microtime(true));
require_once PATH_CORE_FUNC . "common.func.php";
require_once PATH_CORE_CLASS . "lcms.class.php";
require_once PATH_CORE_CLASS . "load.class.php";
define("CLIENT_IP", LCMS::IP());
LOAD::init();
