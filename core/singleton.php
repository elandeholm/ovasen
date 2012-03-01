<?php namespace ovasen\core;

abstract class Singleton {
    protected static $instances = array();
    
    private function __construct($args) {
        // echo "Hello from constructor for " . get_class($this) . PHP_EOL;
        $this->configure($args);
    }

    abstract protected function configure($args);

    public static function getInstance($args=null) {
        $client = get_called_class();

        if (!isset(static::$instances[$client])) static::$instances[$client] = new static($args);
        else if ($args !== null) throw new \Exception ("Singleton is not reconfigurable: " . $client);
        return static::$instances[$client];
    }
}
