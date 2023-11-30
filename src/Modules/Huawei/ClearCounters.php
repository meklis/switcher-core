<?php

namespace SwitcherCore\Modules\Huawei;

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

    /*
     Procedure
    Run the reset counters interface [ interface-type [ interface-number ] ] command to clear interface statistics.
    Run the reset counters if-mib interface [ interface-type [ interface-number ] ] command to clear statistics on the network management interface.
     */
    public function run($params = [])
    {
        if(!$this->console) {
            throw new Exception("Module ClearCounters required console connection");
        }
        $this->status = false;
        try {
            $response = $this->console->exec("reset counters if-mib interface");
            if (preg_match('/(Info: Reset successfully.)/', $response) !== false) {
                $this->status = true;
            } else {
                throw new Exception("Error reset counters, response: " . $response);
            }
        } catch (Exception $e) {
            throw new Exception("error execute command: {$e->getMessage()}", 1, $e);
        }
        return $this;
    }
}