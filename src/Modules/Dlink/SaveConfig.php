<?php


namespace SwitcherCore\Modules\Dlink;



class SaveConfig extends ExecLineCtrl
{
    function getCommandLine($params = [])
    {
        return "save";
    }
}