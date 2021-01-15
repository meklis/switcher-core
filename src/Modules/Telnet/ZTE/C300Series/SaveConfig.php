<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class SaveConfig extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
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