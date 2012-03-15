<?php namespace ovasen\core;

// Marker interface to signal ClassLoader to register class to
// CachedReflector on load

interface Reflected {
    // these methods may NOT trigger ClassLoader or chaos ensues!

    public static function setClassName(ClassName $class_name);
    public static function setReflected($reflected);
    public static function getClassName();
    public static function getReflected();
}
