<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use DI\Annotation\Inject;
use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;


class Reboot extends AbstractModule
{

    protected $status = false;
    /**
     * @Inject
     * @var TelnetLazyConnect
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


    public function run($filter = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module clear counters required telnet connection");
        }
        $this->status = false;
        try {
            $this->telnet->exec("show switch");

            $this->telnet->write("reboot");
            sleep(1);
            $this->telnet->write("y");
            sleep(1);
            $this->status = true;
        } catch (Exception $e) {
            throw new Exception("Error execute command", 1, $e);
        }
        return $this;
    }
}