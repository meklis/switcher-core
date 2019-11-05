<?php


namespace SwitcherCore\Config\Objects;


use SwitcherCore\Modules\AbstractModule;

class Module
{
    protected $name;
    protected $descr;
    protected $arguments;

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
     * @param mixed $name
     * @return Module
     */
    protected function setDescr($descr)
    {
        $this->descr = $descr;
        return $this;
    }

    /**
     * @param mixed $name
     * @return Module
     */
    public function getDescr()
    {
        return $this->descr;
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
                if(!isset($val['name'])) throw new \Exception("Error reading yaml configuration for modules - name is required parameter");
                if(!isset($val['pattern'])) throw new \Exception("Error reading yaml configuration for modules - pattern is required parameter");
                if(!isset($val['required'])) $val['required'] = false;
                if(!isset($val['default'])) $val['default'] = null;
                if(!isset($val['values'])) $val['values'] = null;
                $obj->arguments[$val['name']] = $val;
            }
        } else {
            $obj->arguments = [];
        }
        if(isset($arr['name'])) {
            $obj->setName($arr['name']);
        }
        if(isset($arr['descr'])) {
            $obj->setDescr($arr['descr']);
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
}