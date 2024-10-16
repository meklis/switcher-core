<?php


namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

abstract class LldpInfo extends AbstractInterfaces
{
    protected function formate() {
        $response = [
            'local' => [
                'chassis_id' => null,
                'ports' => null,
            ],
            'remotes' => []
        ];
        if(isset($this->response['lldp.locChassisId'])) {
            if($error = $this->getResponseByName('lldp.locChassisId')->error()) {
                throw new \SNMPException($error);
            }
            $response['local']['chassis_id'] = $this->getResponseByName('lldp.locChassisId')->fetchOne()->getHexValue();
        }
        if(isset($this->response['lldp.locPortId'])) {
            if($error = $this->getResponseByName('lldp.locPortId')->error()) {
                throw new \SNMPException($error);
            }
            $ports = [];
            foreach ($this->getResponseByName('lldp.locPortId')->fetchAll() as $dt) {
                $id = Helper::getIndexByOid($dt->getOid());
                $ports[] = [
                    'port_id' => $id,
                    'ident' => $dt->getHexValue(),
                    'interface' => $this->parseInterface($id),
                ];
            }
            $response['local']['ports'] = $ports;
        }

        $remotes = [];
        if(isset($this->response['lldp.remChassisId'])) {
            if($error = $this->getResponseByName('lldp.remChassisId')->error()) {
                throw new \SNMPException($error);
            }
            foreach ($this->getResponseByName('lldp.remChassisId')->fetchAll() as $dt) {
                $id = Helper::getIndexByOid($dt->getOid());
                $port = Helper::getIndexByOid($dt->getOid(), 1);
                $remotes["{$port}.{$id}"] = [
                    'id' => $id,
                    'interface' => $this->parseInterface($port),
                    'chassis_id' => $dt->getHexValue(),
                    'port_ident' => null,
                    '_port_id' => null,
                ];
            }
        }
        if(isset($this->response['lldp.remPortId'])) {
            if($error = $this->getResponseByName('lldp.remPortId')->error()) {
                throw new \SNMPException($error);
            }
            $remotes = [];
            foreach ($this->getResponseByName('lldp.remPortId')->fetchAll() as $dt) {
                $id = Helper::getIndexByOid($dt->getOid());
                $port = Helper::getIndexByOid($dt->getOid(), 1);
                if(!isset($remotes["{$port}.{$id}"]['chassis_id'])) {
                    $remotes["{$port}.{$id}"]['chassis_id'] = null;
                }
                $remotes["{$port}.{$id}"] = [
                    'id' => $id,
                    'interface' => $this->parseInterface($port),
                    'port_ident' => $dt->getHexValue(),
                    '_port_id' => $this->getPortId($remotes["{$port}.{$id}"]['chassis_id'], $dt->getHexValue()),
                ];
            }
        }
        $response['remotes'] = array_values($remotes);
        return $response;
    }
    function getPretty()
    {
        return $this->formate();
    }

    function getPortId($chassisId, $portIdent)
    {
        if(!$chassisId) return null;
        if(!$portIdent) return null;
        return hexdec(str_replace(':', '', $portIdent)) - hexdec(str_replace(':', '', $chassisId));
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $oids = [];
        if(isset($filter['load_only'])) {
            $loadOnly = explode(",", $filter['load_only']);
            if(in_array("loc_chassis", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.locChassisId');
            }
            if(in_array("loc_ports", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.locPortId');
            }
            if(in_array("rem_chassis", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.remChassisId');
            }
            if(in_array("rem_port", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.remPortId');
            }
        } else {
            $oids = $this->oids->getOidsByRegex('lldp\..*');
        }

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid->getOid());
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
