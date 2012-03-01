<?php namespace ovasen\core;

// Really trivial unit testing protocol
// 
// OK, suppose you wanted to unit test "my_class.php" defining a class named
// MyClass. This is your Testee
// 
// o Create a Tester class named "my_class_tester.php" defining a class
//   name MyClassTester.
// o Tester class extends Test, nothing else
// o Add methods that test specific aspects of Testee. The base class Test
//   automatically gives you an instance of Testee in $this->instance
// o Make sure your Tester method are descriptive and that the names begin with
//   "test", all other methods are ignored. This is useful for utility methods in
//    the Tester
// o Use the $this->assert() & $this->assertEquals() methods inherited from Test
//   to pass invariants back to the Test base class
//
// To run the tests simply:
//   
// o Instatiate the tester, ie.
//   $tester_instance = new MyClassTester();
// o do:
//   $report = $tester_instance->doTests()
// o This method is defined in the Test base class and it returns a simple
//   array based report of all the tests discovered and ran
// o Once you have called $tester_instance->doTests(), you may query
//   $tester_instance->passed(), which returns true iff all tests passed

class Test
{
    private $all_passed;
    private $tester_class_name;
    private $tester_methods;
    private $testee_class_name; // testee class name derived from tester ditto
    protected $instance;        // instance of testee
    const TESTER_CLASS_SUFFIX = "Tester";
    const TESTER_METHOD_PREFIX = "test";
    
    private static function eatSuffix($str, $suffix) {
        $len = strlen($suffix);
        if (substr($str, -$len) === $suffix) {
            return substr($str, 0, strlen($str) - $len);
        }
        return false;        
    }
    
    private static function prefixMatch($str, $prefix) {
        return substr($str, 0, strlen($prefix)) === $prefix;
    }
    
    private function setClassNames() {
        $this->tester_class_name = get_class($this);
        $sans_suffix = self::eatSuffix($this->tester_class_name,
            self::TESTER_CLASS_SUFFIX);
        if ($sans_suffix === false) {
            trigger_error(sprintf("Tester class name \"%s\" does not end in Tester",
                $this->tesster_class_name));
        }
        $this->testee_class_name = $sans_suffix;      
    }
    
    public function __construct() {
        $this->setClassNames();
        // Singleton testees need special treatment!
        // These classes are not meant to be instantiated
        // from the outside, they will crash and burn if you try.
        // This is basically an example of the "marker interface"
        // design pattern
        
        $bases = class_parents($this->testee_class_name);
        if (isset($bases["ovasen\\core\\Singleton"])) {
            $this->instance = call_user_func(array($this->testee_class_name, "getInstance"));
        }
        else {
            $this->instance = new $this->testee_class_name;
        }
        $this->registerTests();
    }
    
    private static function failAssert($type, $description=null) {
        // we return the exception and throw it in assert*(), otherwise we would
        // get another stack frame in the trace which is bother
       
        if ($description !== null) {
            return new TestException(sprintf("%s: failed (%s)", $type, $description));
        }
        else {
            return new TestException(sprintf("%s: failed", $type));
        }
    }
    
    protected function assert($condition, $description = null) {
        if ($condition !== true) {
            throw self::failAssert(__METHOD__, $description);
        }
    }
    
    protected function assertEquals($a, $b, $description = null) {
        if ($a !== $b) {
            throw self::failAssert(__METHOD__, $description);
        }
    }
    
    protected function assertNotEquals($a, $b, $description = null) {
        if ($a === $b) {
            throw self::failAssert(__METHOD__, $description);
        }
    }
    
    protected function assertStringEqual($a, $b, $description = null) {
        if (!is_string($a) || !is_string($b) || strcmp($a, $b)) {
            throw self::failAssert(__METHOD__, $description);
        }
    }
    
    // Register all the test methods in the testee
    
    protected function registerTests() {
        $this->tester_methods = array();
        $methods = get_class_methods($this->tester_class_name);
        foreach ($methods as $method) {
            if (self::prefixMatch($method, "test")) {
                $this->tester_methods[] = $method;
            }
        }
    }
    
    public function doTests($stop_on_error = true) {
        $this->all_passed = true;
        $report = array();
        $stop = false;
        foreach ($this->tester_methods as $method) {
            echo "$method" . PHP_EOL;
            $te = null;
            try {
                $this->$method();
            }
            catch (TestException $te) {
                $this->all_passed = false;
                $stop = $stop_on_error;
                $traces = $te->getTrace();
                $trace = $traces[1]; // $traces[0] is us, [1] is the client
                $report[$method]["testee"] = $this->testee_class_name;
                $report[$method]["tester"] = $this->tester_class_name;
                $report[$method]["message"] = $te->getMessage();
                $report[$method]["file"] = $trace["file"];
                $report[$method]["line"] = $trace["line"];
//                $report[$method]["snippet"] = self::getSnippet($trace["file"], $trace["line"]);
                $report[$method]["xyzzy"] = get_class($this->instance);
            }
            if (!($te instanceof TestException)) {
                $report[$method] = "passed";
            }
            if ($stop) {
                break;
            }
        }
        return $report;
    }
}

class TestException extends \Exception { };
