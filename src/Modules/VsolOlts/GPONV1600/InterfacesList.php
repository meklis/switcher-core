<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


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
        return $this->response;
    }


    public function run($filter = [])
    {
        //$this->response = $this->getInterfaces();
        $this->response = $this->getPhysicalInterfaces();
        return $this;
    }

}

