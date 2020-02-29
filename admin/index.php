<?php
$t = @$_GET['t'] ? $_GET['t'] : "sys";
$n = @$_GET['n'] ? $_GET['n'] : "index";
$c = @$_GET['c'] ? $_GET['c'] : "index";
$a = @$_GET['a'] ? $_GET['a'] : "index";
define("L_TYPE", $t);
define("L_NAME", $n);
define("L_CLASS", $c);
define("L_MODULE", "admin");
define("L_ACTION", "do{$a}");
require_once '../core/route.php';