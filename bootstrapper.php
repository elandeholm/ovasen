<?php namespace ovasen;

use ovasen\core\Error;
use ovasen\core\ClassLoader;
use ovasen\core\ClassLoaderTester;

define("ROOT_PATH", dirname( __FILE__ ));
define("NAME_SPACE", "ovasen");
require "core" . DIRECTORY_SEPARATOR . "singleton.php";
require "core" . DIRECTORY_SEPARATOR . "error.php";
require "core" . DIRECTORY_SEPARATOR . "class_loader.php";

// simplistic unit testing

$tester = new ClassLoaderTester();
$report = $tester->doTests();
print_r($report);
trigger_error("And now you die!");