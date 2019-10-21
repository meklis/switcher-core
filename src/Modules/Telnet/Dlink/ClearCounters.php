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

    public function run($filter = [])
    {
        if(!$this->telnet_conn) {
            throw new \Exception("Module clear counters required telnet connection");
        }
        $this->status = false;
        try {
            $this->telnet_conn->exec("clear counters");
            $this->status  = true;
        } catch (\Exception $e) {
            throw new \Exception("Error clear counters", 1, $e);
        }
        return $this;
    }
}