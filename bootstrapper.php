<?php namespace ovasen;

use ovasen\core\Error;
use ovasen\core\ClassName;
use ovasen\core\ClassLoader;
use ovasen\unit\BaseUnit;
use ovasen\unit\TestUnit;

define("ROOT_PATH", dirname( __FILE__ ));
require "core" . DIRECTORY_SEPARATOR . "singleton.php";
require "core" . DIRECTORY_SEPARATOR . "error.php";
require "core" . DIRECTORY_SEPARATOR . "class_loader.php";

// simplistic unit testing

ClassLoader::getInstance()->addLoader(
        "ovasen" . ClassName::NAMESPACE_DELIMITER . "core" ,
        ROOT_PATH . DIRECTORY_SEPARATOR . "core");
ClassLoader::getInstance()->addLoader(
        "ovasen" . ClassName::NAMESPACE_DELIMITER . "unit" ,
        ROOT_PATH . DIRECTORY_SEPARATOR . "unit");
$test_unit1 = new TestUnit("kalle");
$test_unit2 = new TestUnit("kamel");
//$tester = new ClassLoaderTester();
//$report = $tester->doTests();
//print_r($report);
echo "full name: " . $test_unit1->getFullName() . PHP_EOL;
echo "reflected data: " . print_r($test_unit1->getReflected(), true) . PHP_EOL;
echo "full name: " . $test_unit2->getFullName() . PHP_EOL;
echo "reflected data: " . print_r($test_unit2->getReflected(), true) . PHP_EOL;
echo BaseUnit::debug();
echo ClassLoader::getInstance()->debug();
trigger_error("And now you die!");
