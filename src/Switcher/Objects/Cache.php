<?php


namespace Switcher\Objects;


class Cache
{
    protected $memcache;
    static private $cacheEnabled = false;
    function __construct($config = [])
    {
        if(isset($config['host']) && isset($config['port'])) {
            $this->memcache = new \Memcache;
            $this->memcache->connect($config['host'], $config['port']);
        }

    }
    function get($key) {

    }
    function set($key, $value, $timeout = 3600) {

    }

}