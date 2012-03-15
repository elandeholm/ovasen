<?php namespace ovasen\unit;

use ovasen\core\ClassName;
use ovasen\core\Reflected;
use ovasen\core\CachedReflector;

abstract class BaseUnit implements Reflected
{
    const NAME_GLUE = ":";
    private static $unit_names = array();
    protected static $class_name;
    protected static $reflected;
    private $full_name;
    private $controls;
    private $inputs;
    private $outputs;

    public static final function setClassName(ClassName $class_name) {
        $klass = get_called_class();
        $klass::$class_name = $class_name;
    }

    public static final function setReflected($reflected) {
        $klass = get_called_class();
        $klass::$reflected = $reflected;
    }

    public static final function getClassName() {
        return self::$class_name;
    }

    public static final function getReflected() {
        return self::$reflected;
    }

    public final function getFullName() {
        return $this->full_name;
    }

    public final function __construct($name) {
        $full_name = implode(self::NAME_GLUE, self::$class_name->getNameSpaceParts())
                . self::NAME_GLUE . self::$class_name->getClassBaseName() . self::NAME_GLUE . $name;
        if (isset(BaseUnit::$unit_names[$full_name])) {
            throw new BaseUnitException("There can be only one: " . $full_name);
        }
        BaseUnit::$unit_names[$full_name] = $this;
        $this->full_name = $full_name;
    }

    public static function debug() {
        return print_r(self::$unit_names, true) . PHP_EOL;
    }

    public function connectFrom($source) {
        $source_name = $source->getFullName();
        if (isset($this->inputs[$source->getFullName]))

        $this->inputs[$source->getFullName()] = $source;
    }

    protected function connectTo(BaseUnit $destination) {
        $destination->connectFrom($this);
        $this->outputs[$destination->getFullName] = $destination;
    }

    public function disconnectFrom($source) {

    }
}
?>
