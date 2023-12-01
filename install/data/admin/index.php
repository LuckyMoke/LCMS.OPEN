<?php
$form = @$_GET ?: [];
define("L_TYPE", @$form['t'] ? @strip_tags($form['t']) : "sys");
define("L_NAME", @$form['n'] ? @strip_tags($form['n']) : "index");
define("L_CLASS", @$form['c'] ? @strip_tags($form['c']) : "index");
define("L_MODULE", "admin");
define("L_ACTION", "do" . (@$form['a'] ? @strip_tags($form['a']) : "index"));
require_once '../core/route.php';
