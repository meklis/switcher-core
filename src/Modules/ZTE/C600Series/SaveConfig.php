<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class SaveConfig extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->exec("end");
        $this->exec("wr");
        return $this;
    }


    public function getPretty()
    {
        return true;
    }

    public function getPrettyFiltered($filter = [])
    {
        return true;
    }

}