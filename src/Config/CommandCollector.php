<?php


namespace SwitcherCore\Config;


use SwitcherCore\Config\Objects\Model;

class CommandCollector extends Collector
{

    /**
     * @var Model
     */
    protected $model ;

    protected $commands = [];
    /**
     * @param Model $model
     * @return $this
     */
    function setModel(Model $model){
        $this->model = $model;
        $commands = $this->reader->readCommands($this->model);
        foreach ($commands as $command) {
            $commands[$command->getName()] = $command;
        }
        return $this;
    }

    protected function read()
    {

    }

    function getCommandNames() {
        $names = [];
        foreach ($this->commands as $name=>$_) {
            $names[] = $name;
        }
        return $names;
    }

    function getCommandByName($name) {
        if(isset($this->commands[$name])) {
            return $this->commands[$name];
        }
        throw new \Exception("Command with name $name not found");
    }
}