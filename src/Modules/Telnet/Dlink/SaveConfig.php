<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class SaveConfig extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        return "save";
    }
}