<?php


namespace SwitcherCore\Switcher;


class PhpCache implements CacheInterface
{
    protected static $cache = [];
    public function __construct()
    {

    }

    public function set($key, $value, $timeout = 1) {
        self::$cache[$key] = [
            'value' => $value,
            'timeout' => $timeout + time(),
        ];
        return $this;
    }
    public function get($key) {
        if($this->isExpired($key) === true) {
            return self::$cache[$key]['value'];
        }
        return  null;
    }
    public function isExist($key) {
        return isset(self::$cache[$key]);
    }
    protected function isExpired($key) {
        if($this->isExist($key)) {
            return self::$cache[$key]['timeout'] < time();
        }
        return  null;
    }
}