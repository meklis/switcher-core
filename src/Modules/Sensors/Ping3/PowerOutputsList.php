<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class PowerOutputsList extends AbstractModule
{
    public function run($params = [])
    {
        $oids = array_map(function ($o) {
            return Oid::init($o->getOid());
        }, [
            $this->oids->getOidByName('power.out.name'),
            $this->oids->getOidByName('power.out.mode'),
            ]
        );
        $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        return $this;
    }

    public function getPretty()
    {
        $data = [];
        if(!isset($this->response['power.out.name']) || $this->response['power.out.name']->error()) {
            return [];
        }
        foreach ($this->response['power.out.name']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id] = [
              'id' => $id,
              'name' => $resp->getValue(),
              'mode' => null,
            ];
        }
        foreach ($this->response['power.out.mode']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id]['mode'] = $resp->getParsedValue();
        }
        return array_values($data);
    }

    public function getPrettyFiltered($filter = [])
    {
        if($filter['id']) {
            return  array_values(array_filter($this->getPretty(), function ($el) use ($filter) {
                return $filter['id'] == $el['id'];
            }));
        }
        return $this->getPretty();
    }

}