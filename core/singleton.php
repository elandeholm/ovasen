<?php namespace ovasen\core;

interface Singleton {
// typical implementation:
//    
//    public static function getInstance() {
//        if(is_null(self::$instance)) {
//            self::$instance = new self;
//        }
//        return self::$instance;
//    }
//
// TBD: make Singleton an abstract class instead and extend instead of implement
    
    static public function getInstance();
}
