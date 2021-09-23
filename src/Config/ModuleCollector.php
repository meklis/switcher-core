<?php



namespace SwitcherCore\Config;



use ErrorException;
use Exception;
use SwitcherCore\Config\Objects\Module;

class ModuleCollector extends Collector
{
    /**
     * @var SwitcherCore\Config\Objects\Module[]
     */
    protected $modules;

    /**
     * @return $this
     * @throws ErrorException
     */
    protected function read()
    {
        $modules = [];
        foreach ($this->reader->readModulesConfig() as $module) {
            $modules[$module->getName()] = $module;
        }
        $this->modules = $modules;
        return $this;
    }


    /**
     * @param $name
     * @return Module
     * @throws Exception
     */
    function getByName($name) {
        if(isset($this->modules[$name])) {
            return $this->modules[$name];
        }
        throw new Exception("Module configuration with name $name not found");
    }

    /**
     * @return SwitcherCore\Config\Objects\Module[]
     */
    function getAll() {
        return $this->modules;
    }

}