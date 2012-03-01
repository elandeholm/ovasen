<?php namespace ovasen\core;

use ovasen\core\Test;
use ovasen\core\ClassLoader;

// Silly example of unit testing
// If the ClassLoader was broken enough to not pass
// the tests given below, we would never get here in
// the first place. :-/

class ClassLoaderTester extends Test
{
    public function testFileNameToFqClassName() {
        $file_name = "core" . DIRECTORY_SEPARATOR . "singleton.php";
        $class_name = $this->instance->fileNameToFqClassName($file_name);
        $reference = "ovasen" . ClassLoader::NAMESPACE_DELIMITER .
            "core" . ClassLoader::NAMESPACE_DELIMITER . "Singleton";
        
        $this->assert(!strcmp($class_name, $reference));
    }
    public function testYesYesYes() {
        $this->assertEquals(42, 42);
    }
    public function testImpossible() {
        $this->assertEquals(17, 42);
    }
    public function testNotRan() {
        $this->assert(false);
    }
}