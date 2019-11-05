<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class SpeedPortControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        if($params['port'] > $this->obj->model->getPorts()) {
            throw new \InvalidArgumentException("Max number of port is {$this->obj->model->getPorts()}");
        }
        return "config ports {$params['port']} speed {$this->getRevertSpeed($params['speed'])}";
    }
}