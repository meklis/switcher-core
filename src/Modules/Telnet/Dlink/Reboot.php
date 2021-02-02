<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;


class Reboot extends AbstractModule
{

    protected $status = false;
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
        if(!$this->telnet) {
            throw new \Exception("Module clear counters required telnet connection");
        }
        $this->status = false;
        try {
           $this->telnet->setPrompt('Command:')->exec("reboot\ny");
           $this->status = true;
        } catch (\Exception $e) {
            throw new \Exception("Error execute command", 1, $e);
        }
        return $this;
    }
}