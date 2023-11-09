<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;

class DeregOnt extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $iface = $this->parseInterface($params['interface']);
        $interface = "{$iface['_technology']}_olt-{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}";
        $this->exec("conf t");
        $this->exec("interface {$interface}");
        $this->exec("no onu {$iface['_onu']}");
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