<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class ResetPonPort extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required console connection");
        }
        $interface = $this->parseInterface($params['interface']);
        if ($interface['type'] !== 'PON') {
            throw new \Exception("Allow to reset port only");
        }

        $this->exec("conf t");
        $this->exec("interface {$interface['_technology']}-olt_{$interface['_shelf']}/{$interface['_slot']}/{$interface['_port']}");
        $this->exec("shutdown");
        $this->exec("reset");
        $this->exec("no shutdown");
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