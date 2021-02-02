<?php


namespace SwitcherCore\Modules\Telnet\Dlink;


class SpeedPortControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        if($params['port'] > $this->model->getPorts()) {
            throw new \InvalidArgumentException("Max number of port is {$this->model->getPorts()}");
        }
        return "config ports {$params['port']} speed {$this->getRevertSpeed($params['speed'])}";
    }
}