<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\AbstractModule;

class PowerOutputControl extends AbstractModule
{
    public function run($params = [])
    {
        $data = $this->getModule('power_control_output_list')->run()->getPrettyFiltered(['id' => $params['id']]);
        $state = null;
        if(isset($data[0])) {
            $state = $data[0];
        } else {
            throw new \Exception("Error get current state of power output");
        }
        if($params['name'] !== null && $state['name'] !== trim($params['name'])) {
            $this->setName($state['id'], $params['name']);
        }
        if($params['mode'] && $state['mode'] !== trim($params['mode'])) {
            $this->setMode($state['id'], $params['mode']);
        }
        $data = $this->getModule('power_control_output_list')->run()->getPrettyFiltered(['id' => $params['id']]);
        $this->response = $data[0];
        return $this;
    }

    function setName($id, $name)
    {
        $aliasOid = $this->oids->getOidByName('power.out.name')->getOid() . ".{$id}";
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

    function setMode($id, $mode)
    {
        $oid = $this->oids->getOidByName('power.out.mode');
        $modeId = $oid->getValueIdByName($mode);

        $setOid = $this->oids->getOidByName('power.out.mode')->getOid() . ".{$id}";
        $resp = $this->snmp->set(
            Oid::init($setOid,
                false,
                PoollerRequest::TypeIntegerValue,
                $modeId
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update name - {$r->error}");
            }
        }
        $this->response = [
            'mode' => $mode,
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