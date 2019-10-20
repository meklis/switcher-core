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

    public function walk($filter = [])
    {
        $this->status = false;
        try {
           $this->conn->setPrompt('Command:')->exec("reboot\ny");
        } catch (\Exception $e) {
            throw new \Exception("Error execute command", 1, $e);
        }
        return $this;
    }
}