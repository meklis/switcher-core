<?php


namespace SwitcherCore\Config\Objects;


use SwitcherCore\Modules\AbstractModule;

class Module
{
    protected $name;
    protected $arguments;
    protected $dependency_modules = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Module
     */
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param mixed $arguments
     * @return Module
     */
    protected function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    protected  function __construct(){}

    static function init($arr) {
        $obj = new self();
        if(isset($arr['arguments'])) {
            foreach ($arr['arguments'] as $val) {
                $obj->arguments[$val['name']] = $val;
            }
        } else {
            $obj->arguments = [];
        }
        if(isset($arr['name'])) {
            $obj->setName($arr['name']);
        }
        if(isset($arr['dependency_modules'])) {
            $obj->dependency_modules = $arr['dependency_modules'];
        }
        return $obj;
    }

    function validate(&$arguments) {
        foreach ($arguments as $key=>$val) {
            if(!isset($this->arguments[$key])) {
                unset($arguments[$key]);
            } else {
                if (!preg_match("/{$this->arguments[$key]['pattern']}/",$val)) {
                    throw new \InvalidArgumentException("Send incorrect $key parameter, pattern check failed");
                }
            }
        }
        foreach ($this->arguments as $key=>$val) {
            if(!isset($arguments[$key]) && $val['required']) {
                throw new \InvalidArgumentException("Parameter $key is required");
            }
            if(!isset($arguments[$key]) || !$arguments[$key]) {
                $arguments[$key] = isset($val['default']) ? $val['default'] : '';
            }

        }
        return $this;
    }
    function getDependencyModules() {
        return $this->dependency_modules;
    }
}