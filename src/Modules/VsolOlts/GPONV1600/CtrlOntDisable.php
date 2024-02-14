<?php

namespace SwitcherCore\Modules\VsolOlts\GPONV1600;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class CtrlOntDisable extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $interface = $this->parseInterface($filter['interface']);
        if ($interface['type'] != 'ONU') {
            throw new \InvalidArgumentException("Allow only for ONU");
        }

        $state = null;
        $oid = $this->oids->getOidByName('ont.actions.action');
        if($filter['state']) {
            foreach ($oid->getValues() as $id=>$value) {
                if($value == $filter['state']) {
                    $state = $id;
                    break;
                }
            }
        }

        $this->checkSnmpRespError($this->snmp->set(Oid::init($this->oids->getOidByName('ont.actions.port')->getOid(), false, 'Integer', $interface['_port'])));
        $this->checkSnmpRespError($this->snmp->set(Oid::init($this->oids->getOidByName('ont.actions.ont')->getOid(), false, 'Integer', $interface['_onu'])));
        $this->checkSnmpRespError($this->snmp->set(Oid::init($this->oids->getOidByName('ont.actions.action')->getOid(), false, 'Integer', $state)));

        return $this;
    }

    function getPretty()
    {
        return true;
    }
}

