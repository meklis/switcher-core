<?php


namespace SwitcherCore\Switcher\Objects;

use SnmpWrapper\Walker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Modules\AbstractModule;


/**
 *
 * Class ObjectStore
 *
 *
 * @package SwitcherCore\Switcher\Objects
 */
class ObjectStore
{

    protected $objects = [];
    function __construct()
    {

    }
    function __set($name, $value)
    {
        return $this->set($name, $value);
    }
    function __get($name)
    {
        return $this->get($name);
    }

    function set($objName, $object) {

        $this->objects[$objName] = $object;
        return $this;
    }
    function get($objName) {
        if($this->isExist($objName)) {
            return $this->objects[$objName];
        }
        throw new \Exception("Called object $objName not exist in object store");
    }
    function isExist($objName) {
        return isset($this->objects[$objName]);
    }

}