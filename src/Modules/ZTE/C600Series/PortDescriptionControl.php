<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SnmpWrapper\Oid;

class PortDescriptionControl extends ModuleAbstract
{

    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);

        if ($interface['type'] == 'ONU') {
            throw new \InvalidArgumentException("ONU not allowed, just physical ports");
        }

        $description = str_replace(' ', '_', $params['description']);
        $this->checkSnmpRespError($this->snmp->set(
            Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$interface['_xid']}")
                ->setType('StringValue')
                ->setValue($description)
        ));

        $this->response = true;
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}