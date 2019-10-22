<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class ClearCounters extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
       return "clear counters";
    }
}