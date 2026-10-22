<?php

namespace SwitcherCore\Modules\Arista;

use SwitcherCore\Modules\General\ConsoleArpPing;

class ArpPing extends ConsoleArpPing
{
    protected function getPingCommand($ip, $count)
    {
        return "ping {$ip} repeat {$count} timeout 1";
    }
}
