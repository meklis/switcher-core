<?php


namespace Switcher\Config;


abstract class Collector
{
    /**
     * @var Collector
     */
    protected static $instance;

    /**
     * @var Reader
     */
    public $reader;
    protected function __construct(){}
    public static function init(Reader $reader) {
        $instance = new static();
        $instance->reader = $reader;
        $instance->read();
        self::$instance = $instance;
        return self::$instance;
    }
    public static function getInstance() {
        return self::$instance;
    }
    protected abstract function read();
}