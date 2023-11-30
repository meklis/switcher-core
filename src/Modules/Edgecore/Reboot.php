<?php

namespace SwitcherCore\Modules\Edgecore;

use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class Reboot extends AbstractModule
{
    protected $status = false;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $telnet;

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
        return $this;
        if(!$this->telnet) {
            throw new Exception("Module Edgecore/Reboot required console connection");
        }
        $this->status = false;
        try {
            $response = $this->telnet->exec("reload");
            sleep(1);
            $this->telnet->write("y");
            sleep(1);
            $this->status = true;
        } catch (Exception $e) {
            throw new Exception("error execute command: {$e->getMessage()}", 1, $e);
        }
        return $this;
    }
}