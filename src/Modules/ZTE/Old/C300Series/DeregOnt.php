<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class DeregOnt extends ModuleAbstract
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