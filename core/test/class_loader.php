<?php namespace ovasen\core\test;

use \ovasen\core\Test;

class ClassLoader extends Test
{
    public function test1() {
        $file_name = "core" . DIRECTORY_SEPARATOR . "singleton.php";
        $class_name = $this->instance->fileNameToFqClassName($file_name);
        $reference = "ovasen" . \ovasen\core\ClassLoader::NAMESPACE_DELIMITER .
            "core" . \ovasen\core\ClassLoader::NAMESPACE_DELIMITER . "Singleton";
        
        echo "class_name: " . $class_name . PHP_EOL;
        echo "reference: " . $reference . PHP_EOL;
        
        
        $this->assert(!strcmp($class_name, $reference));
    }
}