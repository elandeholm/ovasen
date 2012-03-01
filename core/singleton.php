<?php namespace ovasen\core;

abstract class Singleton {
    static protected $instance;

    // protected: you may override me but you cannot call me from the outside!
    protected function __construct() {
        if (isset(self::$instance)) {
            trigger_error("There can be only one " . get_class($this));
        }
        self::$instance = $this;
    }

    public static function getInstance($args = null) {
        if (!isset(self::$instance)) self::$instance = new static($args);
        else if ($args !== null) trigger_error("Singleton is not reconfigurable");
        return self::$instance;
    }
}
