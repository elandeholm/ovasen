<?php namespace ovasen\core;

class Error extends Singleton {
    public function _errorHandler($errno, $errstr, $errfile, $errline) {
        echo "$errstr $errfile:$errline" . PHP_EOL;
        die();  // Yep, die, even on E_NOTICE. This way we don't get pages and pages of crap
                // in the cmd window on errors.
    }
    
    protected function configure($args=array()) {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_error', TRUE);
        set_error_handler(array($this, "_errorHandler"));
    }
}

@Error::getInstance();
