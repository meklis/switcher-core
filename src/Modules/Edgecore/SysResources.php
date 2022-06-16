<?php

namespace SwitcherCore\Modules\Edgecore;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;

class SysResources extends AbstractModule
{
    use InterfacesTrait;


    public function run($params = [])
    {
        $oids = [];
        foreach ($this->oids->getOidsByRegex('resources\..*') as $oid) {
            $oids[] = Oid::init($oid->getOid());
        }
        $response = $this->formatResponse($this->snmp->walk($oids));
        $this->response = [
            'cpu' => [
                'util' => (int)$this->getResponseByName('resources.cpuUtil', $response)->fetchAll()[0]->getValue(),
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' => null,
            'memory' => [
                'util' =>  100 - (int)$this->getResponseByName('resources.memFreePrc', $response)->fetchAll()[0]->getValue(),
            ],
        ];
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