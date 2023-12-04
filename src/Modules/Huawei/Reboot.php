<?php

namespace SwitcherCore\Modules\Huawei;

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
        if(!$this->console) {
            throw new Exception("Module Huawei/Reboot required console connection");
        }
        $this->status = false;
        try {
            $this->console->exec("display device");

            $this->console->write("reboot");
            sleep(1);
            $this->console->write("y");
            sleep(1);
            $this->status = true;
        } catch (Exception $e) {
            throw new Exception("error execute command: {$e->getMessage()}", 1, $e);
        }
        return $this;
    }
}