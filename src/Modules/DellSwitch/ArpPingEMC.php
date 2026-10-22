<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Modules\General\ConsoleArpPing;

class ArpPingEMC extends ConsoleArpPing
{
    protected function getPingCommand($ip, $count)
    {
        return "ping -c {$count} -W 1 {$ip}";
    }
}
