<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SwitcherCore\Modules\General\ConsoleArpPing;

class ArpPing extends ConsoleArpPing
{
    protected function getPingCommand($ip, $count)
    {
        return "ping count {$count} {$ip}";
    }
}
