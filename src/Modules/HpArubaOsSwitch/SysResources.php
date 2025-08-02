<?php

namespace SwitcherCore\Modules\HpArubaOsSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;

class SysResources extends AbstractModule
{
    use InterfacesTrait;


    public function run($params = [])
    {
        $id = $this->model->getExtraParamByName('sys_resources_id');
        $oids = [];
        foreach ($this->oids->getOidsByRegex('resources\..*') as $oid) {
            $oids[] = Oid::init($oid->getOid() . ".{$id}");
        }
        $response = $this->formatResponse($this->snmp->get($oids));
        $this->response = [
            'cpu' => [
                'util' => (int)$this->getResponseByName('resources.cpuUtil', $response)->fetchAll()[0]->getValue(),
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' => null,
            'memory' => [
                'util' =>  (int)$this->getResponseByName('resources.memUtil', $response)->fetchAll()[0]->getValue(),
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