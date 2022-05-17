<?php


namespace SwitcherCore\Modules\Dlink;


class ClearCounters extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
       return "clear counters";
    }
}