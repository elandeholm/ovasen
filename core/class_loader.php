<?php namespace ovasen\core;

require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "singleton.php";

class ClassLoader implements Singleton {
    const NAMESPACE_DELIMITER = "\\";
    const PHP_EXT = ".php";

    static private $instance;
    static private $cache;
    static private $root_path;
    static private $name_space;
    
    public function __construct($root_path, $name_space) {
        if (is_null(self::$instance)) {
            self::$instance = $this;
            self::$cache = array();
            self::$root_path = $root_path;
            self::$name_space = $name_space;
            spl_autoload_register(array($this, "load"));
        }
        else {
            trigger_error("There can be only one ClassLoader"); 
        }
    }

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function includeFile($file_name, $class_name) {
        if (!isset($this->cache[$class_name])) {
            if (!file_exists($file_name)) {
                return false;
            }
            include $file_name;
            $this->cache[$class_name] = $file_name;
        }
        return true;
    }

    private function checkParts(&$parts) {
        //echo "checkParts: " . print_r($parts, true) . PHP_EOL;        
        
        if (count($parts) < 1 || strcasecmp($parts[0], self::$name_space)) {
            return false;
        }
        $new_parts = array();
        $skip = true; // ignore self::$name_space part
        foreach ($parts as $part) {
            if (!$skip) {
                $lc_part = strtolower($part);
                if (!ctype_alnum($lc_part)) {
                    return false;
                }
                $new_parts[] = $lc_part;
            }
            else {
                $skip = false;
            }
        }
        $parts = $new_parts;
        
        return true;
    }

    public function fqClassNameToFileName($fq_class_name, $check_exists=true) {
        $parts = explode(self::NAMESPACE_DELIMITER, $fq_class_name);
        $class_name = array_pop($parts);
        
        if (!$this->checkParts($parts)) {
            return false; // Not us, pass the bucket to the next autoloader
        }
        
        // echo "class_name is $class_name" . PHP_EOL;
        
        $cn = array();
        for ($i = 0; $i < strlen($class_name); ++$i) {
            $c = $class_name{$i};
            if ($i > 0 && ctype_upper($c)) {
                $cn[] = "_";
            }
            $cn[] = strtolower($c);
        }

        // echo "cn is: " . print_r($cn, true) . PHP_EOL;        
        
        $path = "";
        foreach ($parts as $part) {
            $path .= strtolower($part) . DIRECTORY_SEPARATOR; 
        }

        $file_name = sprintf("%s%s%s%s%s",
            ROOT_PATH, DIRECTORY_SEPARATOR, $path, implode($cn), self::PHP_EXT);
        
        if ($check_exists && !file_exists($file_name)) {
            return false;
        }
        return $file_name;
    }

    public function fileNameToFqClassName($file_name, $check_exists=true) {
        if (substr($file_name, -4) === self::PHP_EXT) {
            $file_name = substr($file_name, 0, -4);
            echo "file name: " . $file_name . PHP_EOL;
        }
        if (substr($file_name, 0, 1) === DIRECTORY_SEPARATOR) {
            // absolute path, match ROOT_PATH or fail
            $root_path = substr($file_name, 0, strlen(ROOT_PATH));
            if($root_path !== ROOT_PATH) {
                return false;
            }
            $file_name = substr($file_name, strlen(ROOT_PATH));
            if (count($file_name) < 1 || $file_name[0] !== DIRECTORY_SEPARATOR) {
                return false;
            }
            $file_name = substr($file_name, 1);
        }
        
        $parts = explode(DIRECTORY_SEPARATOR, $file_name);
        $parts = array_merge(array(self::$name_space), $parts);
        
        $class_name = array_pop($parts);
        if(!$this->checkParts($parts)) {
            return false;
        }
        $path = implode(self::NAMESPACE_DELIMITER, $parts);
        
        echo "path: " . print_r($path, true) . PHP_EOL;
        echo "class name: " . print_r($class_name, true) . PHP_EOL;
        echo "parts: " . print_r($parts, true) . PHP_EOL;
        
        $cn = array();
        $cap_next = true;
        for ($i = 0; $i < strlen($class_name); ++$i) {
            $c = $class_name{$i};
            if ($c == "_") {
                $cap_next = true;
            }
            else {
                $l = strtolower($c);
                $cn[] = $cap_next ? strtoupper($c) : $l;
                $cap_next = false;
            }
        }
        $fq_class_name = sprintf("%s%s%s%s%s",
            self::$name_space, self::NAMESPACE_DELIMITER,
            $path, self::NAMESPACE_DELIMITER, implode($cn));
        if ($check_exists) {
            $file_name = $this->fqClassNameToFileName($fq_class_name);
            if (!file_exists($file_name)) {
                return false;
            }
        }
        return $fq_class_name;
    }
    
    public function load($fq_class_name) {
        $file_name = $this->fqClassNameToFileName($fq_class_name);
        if ($file_name === false) {
            return false;
        }
        if ($this->includeFile($file_name, $fq_class_name) === false) {
            trigger_error("Can't load class: $fq_class_name, file: $file_name does not exist!");
        }
    }
}
