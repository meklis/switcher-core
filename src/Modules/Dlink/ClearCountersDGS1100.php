<?php


namespace SwitcherCore\Modules\Dlink;


class ClearCountersDGS1100 extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
       return "clear counters 1-{$this->model->getPorts()}";
    }
}