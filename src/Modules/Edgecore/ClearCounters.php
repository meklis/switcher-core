<?php

namespace SwitcherCore\Modules\Edgecore;

use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class ClearCounters extends AbstractModule
{
    use InterfacesTrait;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    function getPretty()
    {
        return true;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

    public function run($params = [])
    {
        foreach ($this->getInterfacesIds() as $port) {
            $this->console->exec('clear counters ethernet '.$port['_unit'].'/'.$port['_port']);
        }
        return $this;
    }
}