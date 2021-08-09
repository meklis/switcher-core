<?php


namespace SwitcherCore\Modules\Telnet\Dlink;


use InvalidArgumentException;

class SpeedPortControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        return "config ports {$interface['_key']} speed {$this->getRevertSpeed($params['speed'])}";
    }
}