<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class DigitalLinesList extends AbstractModule
{
    public function run($params = [])
    {
        $oids = array_map(function ($o) use ($params) {
            $id = '';
            if(isset($params['id']) && $params['id']) {
                $id .= ".{$params['id']}";
            }
            return Oid::init($o->getOid() . $id);
        }, [
            $this->oids->getOidByName('digital.lines.name'),
            $this->oids->getOidByName('digital.lines.direction'),
            $this->oids->getOidByName('digital.lines.output'),
            $this->oids->getOidByName('digital.lines.value'),
            ]
        );
        if(isset($params['id']) && $params['id']) {
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        }
        return $this;
    }

    public function getPretty()
    {
        $data = [];
        if(!isset($this->response['digital.lines.name']) || $this->response['digital.lines.name']->error()) {
            return [];
        }
        foreach ($this->response['digital.lines.name']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id] = [
              'id' => $id,
              'name' => $resp->getValue(),
              'direction' => null,
              'output' => null,
              'value' => null,
            ];
        }
        foreach ($this->response['digital.lines.direction']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id]['direction'] = $resp->getParsedValue();
        }
        foreach ($this->response['digital.lines.output']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id]['output'] = $resp->getParsedValue();
        }
        foreach ($this->response['digital.lines.value']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id]['value'] = $resp->getParsedValue();
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