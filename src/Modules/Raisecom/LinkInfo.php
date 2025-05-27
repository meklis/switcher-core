<?php

namespace SwitcherCore\Modules\Raisecom;

use SnmpWrapper\Oid;

class LinkInfo extends \SwitcherCore\Modules\General\Switches\LinkInfo {
    use InterfacesTrait;

    public function run($filter = []) {
        $data = [
            $this->oids->getOidByName('if.HighSpeed')->getOid(),
            $this->oids->getOidByName('if.Type')->getOid(),
            $this->oids->getOidByName('if.LastChange')->getOid(),
            $this->oids->getOidByName('if.OperStatus')->getOid(),
            $this->oids->getOidByName('if.AdminStatus')->getOid(),
            $this->oids->getOidByName('if.StatsDuplexStatus')->getOid(),
        ];

        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($data as $num => $d) {
                $data[$num] .= ".{$interface['_snmp_id']}";
            }
            $oidObjects = [];
            foreach ($data as $oid) {
                $oidObjects[] = Oid::init($oid);
            }
            $this->snmp->setOidIncreasingCheck(false);
            $this->response = $this->formatResponse($this->snmp->get($oidObjects));
            $this->snmp->setOidIncreasingCheck(true);
            return $this;
        }
        $oidObjects = [];
        foreach ($data as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->snmp->setOidIncreasingCheck(false);
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        $this->snmp->setOidIncreasingCheck(true);
        return $this;
    }
}
