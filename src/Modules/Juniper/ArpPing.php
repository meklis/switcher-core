<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\General\ConsoleArpPing;

class ArpPing extends ConsoleArpPing
{
    protected function getPingCommand($ip, $count)
    {
        return "ping {$ip} count {$count} rapid wait 1";
    }
}
