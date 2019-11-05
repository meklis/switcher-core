<?php



namespace SwitcherCore\Config;



use SwitcherCore\Config\Objects\Module;

class ModuleCollector extends Collector
{
    protected $modules;

    /**
     * @return $this
     * @throws \ErrorException
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
     * @return mixed
     * @throws \Exception
     */
    function getByName($name) {
        if(isset($this->modules[$name])) {
            return $this->modules[$name];
        }
        throw new \Exception("Module configuration with name $name not found");
    }

}