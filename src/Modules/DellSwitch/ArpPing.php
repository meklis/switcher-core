<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Modules\General\ConsoleArpPing;

class ArpPing extends ConsoleArpPing
{
    protected function getPingCommand($ip, $count)
    {
        return "ping {$ip} count {$count} timeout 1";
    }
}
