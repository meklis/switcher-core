<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


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
        if(!$this->telnet_conn) {
            throw new \Exception("Module clear counters required telnet connection");
        }
        $this->status = false;
        try {
           $this->telnet_conn->setPrompt('Command:')->exec("reboot\ny");
        } catch (\Exception $e) {
            throw new \Exception("Error execute command", 1, $e);
        }
        return $this;
    }
}