<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;

class DeregOnt extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $iface = $this->parseInterface($params['onu']);
        $interface = "{$iface['technology']}-olt_{$iface['shelf']}/{$iface['slot']}/{$iface['port']}";
        $this->exec("conf t");
        $this->exec("interface {$interface}");
        $this->exec("no onu {$iface['onu_num']}");
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