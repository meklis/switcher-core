<?php

namespace SwitcherCore\Modules\BDcom\GP3600;

use SnmpWrapper\Oid;
class PortDescriptionControl extends BDcomAbstractModule
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);

        if ($interface['type'] == 'ONU') {
            throw new \InvalidArgumentException("ONU not allowed, just physical ports");
        }

        $description = str_replace(' ', '_', $params['description']);
        $this->checkSnmpRespError($this->snmp->set(
            Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$interface['xid']}")
                ->setType('StringValue')
                ->setValue($description)
        ));

        $this->response = true;
        return $this;
    }

    function getPretty()
    {
        return $this->response;
    }
}