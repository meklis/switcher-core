<?php


namespace SwitcherCore\Config;


abstract class Collector
{
    /**
     * @var Collector
     */
    protected $instance;

    /**
     * @var Reader
     */
    public $reader;
    protected function __construct(){}
    public static function init(Reader $reader) {
        $instance = new static();
        $instance->reader = $reader;
        $instance->read();
        $instance->instance = $instance;
        return $instance;
    }
    protected abstract function read();
}