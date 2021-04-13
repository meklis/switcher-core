<?php


namespace SwitcherCore\Switcher;


interface CacheInterface
{
    /**
     * @param $key
     * @param $value
     * @param int $timeout
     * @return self
     */
    public function set($key, $value, $timeout = -1);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function isExist($key);
}