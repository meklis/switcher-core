<?php


namespace SwitcherCore\Modules\Dlink;


use InvalidArgumentException;

class DescriptionPortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        return "config ports {$interface['_key']} description \"{$params['description']}\"";
    }
}