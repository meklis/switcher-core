<?php


namespace SwitcherCore\Modules\Telnet\Dlink;



use InvalidArgumentException;

class StatePortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        return "config ports {$interface['_key']} state {$params['state']}";
    }
}