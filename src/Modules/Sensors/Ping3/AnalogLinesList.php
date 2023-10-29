<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class AnalogLinesList extends AbstractModule
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
            $this->oids->getOidByName('analog.lines.name'),
            $this->oids->getOidByName('analog.lines.value'),
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
        if(!isset($this->response['analog.lines.name']) || $this->response['analog.lines.name']->error()) {
            return [];
        }
        foreach ($this->response['analog.lines.name']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id] = [
              'id' => $id,
              'name' => $resp->getValue(),
              'value' => null,
            ];
        }
        foreach ($this->response['analog.lines.value']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $data[$id]['value'] = $resp->getParsedValue() / 10;
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