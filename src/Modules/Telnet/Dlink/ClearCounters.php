<?php


namespace SwitcherCore\Modules\Telnet\Dlink;


class ClearCounters extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
       return "clear counters";
    }
}