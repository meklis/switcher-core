<?php

namespace SwitcherCore\Modules\Edgecore;

use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class ClearCounters extends AbstractModule
{
    protected $status = false;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    function getPretty()
    {
        return $this->status;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->status;
    }

    public function run($params = [])
    {
        $this->status = false;
        //clear counters ethernet 1/1
        //var_dump($this->model->getPorts());
        //$this->console->connect($this->device->getIp());
        //$this->console->setAccess($this->device->getLogin(),$this->device->getPassword());
        $this->console->connect($this->device->getIp());
        if(!$this->console) {
            throw new Exception("Module ClearCounters required console connection");
        }

        //$this->console->exec('show line');
        //$this->console->write($this->device->getLogin());
        //$this->console->write($this->device->getPassword());
        //$this->console->write('clear counters ethernet 1/3');
        $this->status = true;

        return $this;
        for($p=1;$p<=$this->model->getPorts();$p++){

        }
        return $this;
    }
}