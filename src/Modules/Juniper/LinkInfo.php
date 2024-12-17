<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class LinkInfo extends \SwitcherCore\Modules\General\Switches\LinkInfo
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
            $this->oids->getOidByName('if.HighSpeed') ,
            $this->oids->getOidByName('if.Type'),
            $this->oids->getOidByName('if.LastChange'),
            $this->oids->getOidByName('if.OperStatus'),
            $this->oids->getOidByName('if.AdminStatus'),
            $this->oids->getOidByName('if.StatsDuplexStatus'),
        ]);
        return $this;
    }

}
