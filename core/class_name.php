<?php namespace ovasen\core;

class ClassName {
    const NAMESPACE_DELIMITER = "\\";
    const CLASS_KEY_DELIMITER = "-"; // would have used ":" but NTFS doesn't dig that
    const PHP_EXT = ".php";

    private $class_base_name;
    private $name_space_parts;
    private $full_class_name;
    private $class_key;

    public function __construct($klass=null) {
        if ($klass !== null) {
            if (is_string($klass)) {
                $this->fromClassName($klass);
            }
            else { // let's hope it's an instance
                $this->fromClassName(get_class($klass));
            }
        }
    }

    public function debug() {
        echo "class base name: " . $this->class_base_name . PHP_EOL;
        echo "namespace parts: { ";
        foreach ($this->name_space_parts as $part) {
            echo "$part ";
        }
        echo "}" . PHP_EOL;
    }

    public function fromClassName($class_name) {
        $class_name = trim($class_name, self::NAMESPACE_DELIMITER);
        $class_name_parts = explode(self::NAMESPACE_DELIMITER, $class_name);
        $this->class_base_name = array_pop($class_name_parts);
        $this->name_space_parts = array_map(function ($s) { return strtolower($s); }, $class_name_parts);
    }

    public function getNameSpaceParts() {
        return $this->name_space_parts;
    }

    public function getClassBaseName() {
        return $this->class_base_name;
    }

    public function getFileBaseName() {
        $cn = array();
        for ($i = 0; $i < strlen($this->class_base_name); ++$i) {
            $c = $this->class_base_name{$i};
            if (!ctype_alnum($c)) {
                throw new ClassLoaderException("Class name must be strictly alpha numeric: \"" . $class_name . "\"");
            }
            if ($i > 0 && ctype_upper($c)) {
                $cn[] = "_";
            }
            $cn[] = strtolower($c);
        }

        return implode($cn). self::PHP_EXT;
    }

    public function getFullClassName() {
        if (!isset($this->full_class_name)) {
            $this->full_class_name = implode(self::NAMESPACE_DELIMITER, $this->name_space_parts)
                . self::NAMESPACE_DELIMITER . $this->class_base_name;
        }
        return  $this->full_class_name;
    }

    public function getClassKey() {
        if (!isset($this->class_key)) {
            $this->class_key = implode(self::CLASS_KEY_DELIMITER, $this->getNameSpaceParts())
            . self::CLASS_KEY_DELIMITER . $this->getFileBaseName();
        }
        return $this->class_key;
    }
}