<?php


namespace SwitcherCore\Modules\GCOM;


use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\GCOM\GCOMAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class PortDescriptionControl extends GCOMAbstractModule
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
        $this->response = array_values(array_filter($this->getPhysicalInterfaces(), function ($p) {
          return $p['type'] === 'PON';
        }));
        return $this;
    }

}

