<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class RebootOnu extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $iface = $this->parsePortByName($params['onu']);
        $interface = "{$iface['technology']}-olt_{$iface['shelf']}/{$iface['slot']}/{$iface['port']}";
        $this->exec("conf t");
        $this->exec("pon-onu-mng {$interface}");
        $this->exec("reboot");
        $this->exec("end");
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