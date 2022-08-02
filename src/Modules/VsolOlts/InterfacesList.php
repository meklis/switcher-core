<?php


namespace SwitcherCore\Modules\VsolOlts;


use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class InterfacesList extends VsolOltsAbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    protected $interfaces;


    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        return $data;
    }

    function getPretty()
    {
        return $this->getPhysicalInterfaces();
    }


    public function run($filter = [])
    {
        $this->response = array_values($this->getInterfacesIds());
        return $this;
    }

}

