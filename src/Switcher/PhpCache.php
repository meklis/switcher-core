<?php


namespace SwitcherCore\Switcher;


class PhpCache implements CacheInterface
{
    protected $cache = [];

    public function set($key, $value, $timeout = 1) {
        $this->cache[$key] = [
            'value' => $value,
            'timeout' => $timeout + time(),
        ];
        return $this;
    }
    public function get($key) {
        if(isset($this->cache[$key]) && $this->cache[$key]['timeout'] > time()) {
            return $this->cache[$key]['value'];
        } else {
            return null;
        }
    }
    public function isExist($key) {
        return isset($this->cache[$key]);
    }
}