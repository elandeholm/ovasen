<?php namespace ovasen\core\test;

use ovasen\core\Reflected;
use ovasen\unit\BaseUnit;

class TheTestClass extends BaseUnit {
    public $kaka = 42;

    public function xyzzy() {
        echo "Reflected is:".  PHP_EOL;
        echo "  " . print_r($this->getReflected(), TRUE) . PHP_EOL;
    }
}

echo "TheTestClazz was loaded!" . PHP_EOL;
