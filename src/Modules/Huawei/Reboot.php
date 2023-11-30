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
            sleep(1);
            $response = $this->console->exec("reboot");
            sleep(1);
            $this->console->write("y");
            sleep(1);
            $this->status = true;
            /*if (preg_match('/(Info: Reset successfully.)/', $response) !== false) {
                $this->status = true;
            } else {
                throw new Exception("Error reset counters, response: " . $response);
            }*/
        } catch (Exception $e) {
            throw new Exception("error execute command: {$e->getMessage()}", 1, $e);
        }
        return $this;
    }
}