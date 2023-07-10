<?php
$form = @$_GET ?: [];
if (!@$form['n']) {
    header("HTTP/1.1 404 Not Found");
    exit();
} else {
    define("L_TYPE", "open");
    define("L_NAME", @strip_tags($form['n']));
    define("L_CLASS", @$form['c'] ? @strip_tags($form['c']) : "index");
    define("L_MODULE", "web");
    define("L_ACTION", "do" . (@$form['a'] ? @strip_tags($form['a']) : "index"));
    require_once '../core/route.php';
}
