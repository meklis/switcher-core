<?php

namespace SwitcherCore\Modules\HPESwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Counters extends \SwitcherCore\Modules\General\Switches\Counters
{
    use InterfacesTrait;
    public function run($params = [])
    {
        $oids = [
            $this->oids->getOidByName('if.HCInOctets')->getOid(),
            $this->oids->getOidByName('if.HCOutOctets')->getOid(),
        ];

        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$interface['_snmp_id']}";
            }
        }

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }

        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
