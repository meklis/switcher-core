<?php

namespace SwitcherCore\Modules\Raisecom;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Counters extends \SwitcherCore\Modules\General\Switches\Counters
{
    use InterfacesTrait;

    public function run($params = []) {
        $oids = [];
        foreach ($this->oids->getOidsByRegex('if\.HC.*') as $oid) {
            $oids[] = $oid->getOid();
        }

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
        
        $this->snmp->setOidIncreasingCheck(false);
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        $this->snmp->setOidIncreasingCheck(true);
        return $this;
    }
}
