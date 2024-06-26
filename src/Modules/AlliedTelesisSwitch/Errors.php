<?php

namespace SwitcherCore\Modules\AlliedTelesisSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Errors extends \SwitcherCore\Modules\General\Switches\Errors
{
    use InterfacesTrait;

    function getPretty()
    {
        return $this->response; // TODO: Change the autogenerated stub
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response; // TODO: Change the autogenerated stub
    }

    public function run($filter = [])
    {
        if(!isset($filter['interface'])) $filter['interface'] = null;
        $rmon = $this->getModule('rmon')->run($filter)->getPrettyFiltered($filter);
        $this->response = array_map(function ($rm) {
            return [
                'interface' => $rm['interface'],
                'in_errors' => $rm['ether_stats_crc_align_errors'] + $rm['ether_stats_fragments'] + $rm['ether_stats_jabber'],
                'out_errors' => $rm['ether_stats_collisions'] + $rm['ether_stats_drop_events'],
            ];
        }, $rmon);
        return $this;
    }


}
