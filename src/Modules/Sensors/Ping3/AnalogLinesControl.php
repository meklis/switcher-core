<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\AbstractModule;

class AnalogLinesControl extends AbstractModule
{
    public function run($params = [])
    {
        $data = $this->getModule('analog_lines_list')->run(['id' => $params['id']])->getPrettyFiltered(['id' => $params['id']]);
        $state = null;
        if(isset($data[0])) {
            $state = $data[0];
        } else {
            throw new \Exception("Error get current state");
        }
        if($params['name'] && $state['name'] !== trim($params['name'])) {
            $this->setName($state['id'], $params['name']);
        }
        $data = $this->getModule('analog_lines_list')->run(['id' => $params['id']])->getPrettyFiltered(['id' => $params['id']]);
        $this->response = $data[0];
        return $this;
    }

    function setName($id, $name)
    {
        $aliasOid = $this->oids->getOidByName('analog.lines.name')->getOid() . ".{$id}";
        $resp = $this->snmp->set(
            Oid::init($aliasOid,
                false,
                PoollerRequest::TypeOctetStringValue,
                $name
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update name - {$r->error}");
            }
        }
        $this->response = [
            'name' => $name,
            'response' => $resp,
        ];
    }
    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}