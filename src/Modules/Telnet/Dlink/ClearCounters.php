<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class ClearCounters extends AbstractModule
{

    protected $status = false;
    function getPretty()
    {
        return true;
    }

    function getPrettyFiltered($filter = [])
    {
        return true;
    }

    public function walk($filter = [])
    {
        $this->status = false;
        try {
            $this->conn->exec("clear counters");
            $this->status  = true;
        } catch (\Exception $e) {
            throw new \Exception("Error clear counters", 1, $e);
        }
        return $this;
    }
}