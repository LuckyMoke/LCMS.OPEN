<?php
if (!@$_GET['n']) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit();
} else {
    $n = @$_GET['n'];
    $c = @$_GET['c'] ? $_GET['c'] : "index";
    $a = @$_GET['a'] ? $_GET['a'] : "index";
    define("L_TYPE", "open");
    define("L_NAME", $n);
    define("L_CLASS", $c);
    define("L_MODULE", "web");
    define("L_ACTION", "do{$a}");
    require_once '../core/route.php';
}
