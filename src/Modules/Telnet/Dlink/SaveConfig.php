<?php


namespace SwitcherCore\Modules\Telnet\Dlink;



class SaveConfig extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        return "save";
    }
}