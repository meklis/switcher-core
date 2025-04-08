<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;

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
