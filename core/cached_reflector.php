<?php namespace ovasen\core;

class CachedReflector extends Singleton
{
    private static $cache;
    private static $class_name;

    protected function configure($args) {
        self::$cache = array();
        self::$class_name = new ClassName(); // this is a reusable resource
    }

    public static function register(ClassName $class_name, $class_file_path) {
        $full_class_name = $class_name->getFullClassName();
        $class_key = $class_name->getClassKey();
        $target_mtime = filemtime($class_file_path);
        $cached_file = ROOT_PATH . DIRECTORY_SEPARATOR . "cache"
            . DIRECTORY_SEPARATOR . "reflection" . DIRECTORY_SEPARATOR . $class_key;

        // because of the semantics of the ClassLoader, we never expect register()
        // to be called other than on class load, ie. just once

        if (isset(self::$cache[$class_key])) {
            throw new CachedReflectorException(
                "CachedReflector: register() invoked more than once for class: " . $full_class_name);
        }

        // pass the class_name object to the reflected class

        call_user_func(array($full_class_name, "setClassName"), $class_name);

        $mtime_cache = false;
        if (file_exists($cached_file)) {
            $mtime_cache = filemtime($cached_file);
        }

        // if a reflection cache does not exist or isn't strictly more recent
        // than the reflected class file

        if (($mtime_cache === false) || ($mtime_cache <= $target_mtime)) {
            $reflected = array();
            $reflected["file"] = $class_file_path;
            $reflected["implements"] = class_implements( $full_class_name );
            $reflected["parents"] = class_parents( $full_class_name );
            $reflected["vars"] = get_class_vars($full_class_name);
            $reflected["methods"] = get_class_methods($full_class_name);

            $cache_fp = fopen($cached_file, "wb");
            $json_encoded = json_encode($reflected);
            fputs($cache_fp, $json_encoded);
            fclose($cache_fp);
        }
        else {
            $cache_fp = fopen($cached_file, "rb");
            $json_encoded = fgets($cache_fp);
            $reflected = json_decode($json_encoded, true);
            fclose($cache_fp);
        }

        // statically pass reflection data to reflected class

        call_user_func(array($full_class_name, "setReflected"), $reflected);

        // in both cases we initialize the volatile static cache

        self::$cache[$class_key] = $reflected;
    }

    public static function get(ClassName $class_name) {
        $class_key = $class_name->getClassKey;
        if (isset(self::$cache[$class_key])) {
            return self::$cache[$class_key];
        }
        throw new CachedReflectorException(
            "CachedReflector: get() called on unregistered class: " . $class_name->getFullClassName());
    }
}

class CachedReflectorException extends \Exception { };

@CachedReflector::getInstance();
