<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class RebootOnu extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
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