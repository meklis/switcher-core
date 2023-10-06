<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Descriptions extends \SwitcherCore\Modules\General\Switches\Descriptions
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

        $oid = $this->oids->getOidByName('if.Alias') ;

        $this->response = $this->snmpGetByInterfaces($ifaces, [$oid]);
        return $this;
    }
}
