<?php


namespace SwitcherCore\Modules\Dlink;


use InvalidArgumentException;

class SpeedPortControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        $interfaces = $this->getModule('link_info')->run(['interface' => $params['interface']])->getPrettyFiltered(['interface' => $params['interface']]);
        $interface = $this->parseInterface($params['interface']);
        if(count($interfaces) > 1) {
            return "config ports {$interface['_key']} speed {$this->getRevertSpeed($params['speed'])} && 
                config ports {$interface['_key']}  medium_type fiber speed {$this->getRevertSpeed($params['speed'])}
            ";
        }
        return "config ports {$interface['_key']} speed {$this->getRevertSpeed($params['speed'])}";
    }
}