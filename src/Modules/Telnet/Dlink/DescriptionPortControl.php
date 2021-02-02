<?php


namespace SwitcherCore\Modules\Telnet\Dlink;


class DescriptionPortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        if($params['port'] > $this->model->getPorts()) {
            throw new \InvalidArgumentException("Max number of port is {$this->model->getPorts()}");
        }
        return "config ports {$params['port']} description {$params['description']}";
    }
}