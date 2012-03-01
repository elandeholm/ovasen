<?php namespace ovasen;

use \ovasen\core\ClassLoader;
use \ovasen\core\test\TheTestClass;
use \ovasen\core\ClassLoaderTester;

define("ROOT_PATH", dirname( __FILE__ ));
define("NAME_SPACE", "ovasen");
require "core" . DIRECTORY_SEPARATOR . "class_loader.php";
$my_class = new TheTestClass();
echo "file_name is " . ClassLoader::getInstance()
    ->fqClassNameToFileName(get_class($my_class)) . PHP_EOL;

// simplistic unit testing

$tester = new ClassLoaderTester();
$report = $tester->doTests();
print_r($report);
