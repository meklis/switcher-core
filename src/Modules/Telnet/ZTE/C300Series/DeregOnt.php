<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class DeregOnt extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $iface = $this->parsePortByName($params['ont']);
        $interface = "{$iface['technology']}-olt_{$iface['shelf']}/{$iface['slot']}/{$iface['port']}";
        $this->exec("conf t");
        $this->exec("interface {$interface}");
        $this->exec("no onu {$iface['onu']}");
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