<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class StatePortControl extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        if($params['port'] > $this->model->getPorts()) {
            throw new \InvalidArgumentException("Max number of port is {$this->model->getPorts()}");
        }
        return "config ports {$params['port']} state {$params['state']}";
    }
}