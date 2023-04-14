<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Errors extends \SwitcherCore\Modules\General\Switches\Errors
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
        $this->response = $this->snmpGetByInterfaces($ifaces, [
            $this->oids->getOidByName('if.InErrors'),
            $this->oids->getOidByName('if.OutErrors'),
            $this->oids->getOidByName('if.InDiscards'),
            $this->oids->getOidByName('if.OutDiscards'),
        ]);
        return $this;
    }
}
