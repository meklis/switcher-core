<?php


namespace SwitcherCore\Modules\Dlink;


use InvalidArgumentException;

class DescriptionPortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        $description = str_replace([" ", '"', "'"], '_', $params['description']);
        return "config ports {$interface['_key']} description {$description}";
    }
}