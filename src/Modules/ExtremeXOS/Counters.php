<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Counters extends \SwitcherCore\Modules\General\Switches\Counters
{
    use InterfacesTrait;
    use WalkerOverGet;
    public function run($filter = [])
    {
        $ifaces = [];
        if($filter['interface']) {
            $ifaces[] = $this->parseInterface($filter['interface']);
        } else {
            $ifaces = $this->getInterfacesIds();
        }
        $this->response = $this->snmpGetByInterfaces($ifaces, $this->oids->getOidsByRegex('if\.HC.*'));
        return $this;
    }
}
