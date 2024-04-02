<?php

namespace SwitcherCore\Modules\JuniperSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Rmon extends \SwitcherCore\Modules\General\Switches\Rmon
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
        $this->response = $this->snmpGetByInterfaces($ifaces,$this->oids->getOidsByRegex('rmon.*'));
        return $this;
    }
}
