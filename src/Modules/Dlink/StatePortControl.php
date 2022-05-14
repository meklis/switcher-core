<?php


namespace SwitcherCore\Modules\Dlink;



use InvalidArgumentException;

class StatePortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        return "config ports {$interface['_key']} state {$params['state']}";
    }
}