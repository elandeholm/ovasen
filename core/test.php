<?php namespace ovasen\core;

use \ovasen\core\TestException;

class Test
{
    private $test_class_name;
    private $test_methods;
    private $class_name;
    protected $instance;
    
    public function __construct() {
        $this->test_class_name = get_class($this);
        
        $parts = explode("\\", $this->test_class_name);
        $class_name = array_pop($parts);
        @array_pop($parts); // gets rid of "test" subdir
        $parts[] = $class_name;
        $this->class_name = implode("\\", $parts);
        $implements = class_implements($this->class_name);
        
        // we use Singleton as a marker interface here
        
        if (isset($implements["ovasen\\core\\Singleton"])) {
            $this->instance = call_user_func(array($this->class_name, "getInstance"));
        }
        else {
            $this->instance = new $this->class_name;
        }
        $this->registerTests();
    }
    
    protected function assert($condition) {
        if ($condition !== true) {
            throw new TestException("assert: condition: $condition");
        }
    }
    
    protected function assertEquals($a, $b) {
        if ($a !== $b) {
            throw new TestException("assertEquals");
        }
    }
    
    protected function registerTests() {
        $this->test_methods = array();
        $methods = get_class_methods($this->test_class_name);
        foreach ($methods as $method) {
            if (substr($method, 0, 4) === "test") {
                $this->test_methods[] = $method;
            }
        }
    }
    
    public function doTests($stop_on_error = true) {
        $report = array();
        $stop = false;
        foreach ($this->test_methods as $method) {
            try {
                $this->$method();
            }
            catch (TestException $te) {
                $stop = $stop_on_error;
                $traces = $te->getTrace();
                $trace = $traces[0];
                $report[$method]["class"] = $this->class_name;
                $report[$method]["test"] = $this->test_class_name;
                $report[$method]["message"] = $te->getMessage();
                $report[$method]["file"] = $trace["file"];
                $report[$method]["line"] = $trace["line"];
                $report[$method]["xyzzy"] = get_class($this->instance);
            }
            if ($stop) {
                break;
            }
        }
        return $report;
    }
}

class TestException extends \Exception { };
