<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;

class KnockSensorState extends AbstractModule
{
    public function run($params = [])
    {
        $oids = array_map(function ($o) {
            return Oid::init($o->getOid());
        }, [
            $this->oids->getOidByName('knock.state'),
            ]
        );
        $this->response = $this->formatResponse($this->snmp->get($oids));
        return $this;
    }

    public function getPretty()
    {
        return [
            'state' => $this->getResponseByName('knock.state')->fetchOne()->getParsedValue()
        ];
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}