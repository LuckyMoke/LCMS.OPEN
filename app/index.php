<?php
$form = @$_GET ?: [];
$form = array_filter($form);
$form = array_merge([
    "n" => "",
    "c" => "index",
    "a" => "index",
], $form);
if (
    !$form['n'] ||
    is_array($form['n']) ||
    is_array($form['c']) ||
    is_array($form['a'])
) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
define("L_TYPE", "open");
define("L_NAME", strip_tags($form['n']));
define("L_CLASS", strip_tags($form['c']));
define("L_MODULE", "web");
define("L_ACTION", "do" . strip_tags($form['a']));
require_once '../core/route.php';
