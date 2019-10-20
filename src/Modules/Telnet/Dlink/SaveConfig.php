<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class SaveConfig extends AbstractModule
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
           $response = $this->conn->exec("save");
           if (strpos($response, "Success") !== false) {
               $this->status = true;
           }
        } catch (\Exception $e) {
            throw new \Exception("Error execute command", 1, $e);
        }
        return $this;
    }
}