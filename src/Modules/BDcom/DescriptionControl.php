<?php

namespace SwitcherCore\Modules\BDcom;

use SnmpWrapper\Oid;

class DescriptionControl extends BDcomAbstractModule
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);


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