<?php namespace ovasen;

use \ovasen\core\ClassLoader;
use \ovasen\core\test\TheTestClass;

define("ROOT_PATH", dirname(__FILE__) );
define("NAME_SPACE", "ovasen");
require "core" . DIRECTORY_SEPARATOR . "class_loader.php";
$cl = new ClassLoader(ROOT_PATH, NAME_SPACE);
$my_class = new TheTestClass();
echo "file_name is " . $cl->fqClassNameToFileName(get_class($my_class)) . PHP_EOL;

// simplistic unit testing

$testcase = new \ovasen\core\test\ClassLoader();
$report = $testcase->doTests();
print_r($report);
