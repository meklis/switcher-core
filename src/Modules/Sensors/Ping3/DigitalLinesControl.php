<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\AbstractModule;

class DigitalLinesControl extends AbstractModule
{
    public function run($params = [])
    {
        $data = $this->getModule('digital_lines_list')->run(['id' => $params['id']])->getPrettyFiltered(['id' => $params['id']]);
        $state = null;
        if(isset($data[0])) {
            $state = $data[0];
        } else {
            throw new \Exception("Error get current state of power output");
        }
        if($params['name'] && $state['name'] !== trim($params['name'])) {
            $this->setName($state['id'], $params['name']);
        }
        if($params['direction'] && $state['direction'] !== trim($params['direction'])) {
            $this->setDirection($state['id'], $params['direction']);
        }
        if($params['output'] && $state['output'] !== trim($params['output'])) {
            $this->setOutput($state['id'], $params['output']);
        }
        $data = $this->getModule('digital_lines_list')->run(['id' => $params['id']])->getPrettyFiltered(['id' => $params['id']]);
        $this->response = $data[0];
        return $this;
    }

    function setName($id, $name)
    {
        $aliasOid = $this->oids->getOidByName('digital.lines.name')->getOid() . ".{$id}";
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

    function setDirection($id, $direction)
    {
        $oid = $this->oids->getOidByName('digital.lines.direction');
        $directionID = $oid->getValueIdByName($direction);

        $setOid = $oid->getOid() . ".{$id}";
        $resp = $this->snmp->set(
            Oid::init($setOid,
                false,
                PoollerRequest::TypeIntegerValue,
                $directionID
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update direction - {$r->error}");
            }
        }
        $this->response = [
            'direction' => $direction,
            'response' => $resp,
        ];
    }
    function setOutput($id, $output)
    {
        $oid = $this->oids->getOidByName('digital.lines.output');
        $outputID = $oid->getValueIdByName($output);

        $setOid = $oid->getOid() . ".{$id}";
        $resp = $this->snmp->set(
            Oid::init($setOid,
                false,
                PoollerRequest::TypeIntegerValue,
                $outputID
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update output - {$r->error}");
            }
        }
        $this->response = [
            'output' => $output,
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