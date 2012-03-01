<?php namespace ovasen;

error_reporting(E_ALL | E_STRICT);
ini_set('display_error', TRUE);

function myErrorHandler($errno, $errstr, $errfile, $errline) {
    echo "$errstr $errfile:$errline" . PHP_EOL;
    die();
}

set_error_handler('ovasen\myErrorHandler');

use \ovasen\core\ClassLoader;
use \ovasen\core\test\TheTestClass;
use \ovasen\core\ClassLoaderTester;

define("ROOT_PATH", dirname( __FILE__ ));
define("NAME_SPACE", "ovasen");
require "core" . DIRECTORY_SEPARATOR . "class_loader.php";

// simplistic unit testing

$tester = new ClassLoaderTester();
$report = $tester->doTests();
print_r($report);
