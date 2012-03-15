<?php namespace ovasen\core;

require "reflected.php";
require "class_name.php";

use ovasen\core\ClassName;

class ClassLoader extends Singleton {
    private static $cache;
    private static $loaders;

    protected function configure($args=array()) {
        if (isset($args["loaders"])) {
            self::$loaders = $args["loaders"];
        }
        self::$cache = array();
        spl_autoload_register(array($this, "load"));
    }

    public function addLoader($name_space, $dir_name) {
        self::$loaders[$name_space][] = $dir_name;
    }

    public function debug() {
        return "ClassLoader: " . print_r ( array( self::$cache, self::$loaders), true) . PHP_EOL;
    }

    private function includeClassFile(ClassName $class_name, $class_file_path, $name_space_key, $sub_dir) {
        $class_key = $class_name->getClassKey();
        if (isset($cache[$class_key])) {
            throw new ClassLoaderException("Trying to include class file twice: " . $class_file_path);
        }
        // we know the class file exists at this point
        require $class_file_path;
        $full_class_name = $class_name->getFullClassName();
        $implements = class_implements( $class_name->getFullClassName(), false);
        if (isset($implements["ovasen\\core\\Reflected"])) {
            // marker interface Reflected means the class wants to be reflected
            CachedReflector::register($class_name, $class_file_path);
        }

        self::$cache[$class_key] = array($name_space_key, $sub_dir, $class_file_path);
    }

    private function findClassFile(ClassName $class_name) {
        $name_space_parts = $class_name->getNameSpaceParts();
        $file_base_name = $class_name->getFileBaseName();
        $path_parts = array();
        $match = false;

        while (count($name_space_parts) > 0) {
            $name_space_key = implode(ClassName::NAMESPACE_DELIMITER, $name_space_parts);
            if (isset(self::$loaders[$name_space_key])) {
                // [name_space_key] set, so expect match
                $match = true;
                $sub_dir = "";
                if (count($path_parts) > 0) {
                    $sub_dir = implode(DIRECTORY_SEPARATOR, array_reverse($path_parts)) . DIRECTORY_SEPARATOR;
                }
                foreach (self::$loaders[$name_space_key] as $path) {
                    $class_file_path = $path . DIRECTORY_SEPARATOR . $sub_dir . $file_base_name;
                    if (file_exists($class_file_path)) {
                        return array( $class_file_path, $name_space_key, $sub_dir );
                    }
                }
            }

            $path_parts[] = array_pop($name_space_parts);
        }
        if ($match === true) {
            // we had at least one name space match but failed to find the class file
            throw new ClassLoaderException("Expected to find class file: " .  $file_name);
        }
        // otherwise just pass the bucket to the next auto_loader
        return false;
    }

    public function load($class_name) {
        $class_name = new ClassName($class_name);
        list ($class_file_name, $name_space_key, $sub_dir) = $this->findClassFile($class_name);

        if ($class_file_name !== false) {
            $this->includeClassFile($class_name, $class_file_name, $name_space_key, $sub_dir);
        }
        else {
            return false;
        }
    }
}

class ClassLoaderException extends \Exception { };

@ClassLoader::getInstance();
