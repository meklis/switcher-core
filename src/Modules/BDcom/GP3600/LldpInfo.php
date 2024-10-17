<?php
namespace SwitcherCore\Modules\BDcom\GP3600;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
class LldpInfo extends BDcomAbstractModule
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
            $response['local']['chassis_id'] = $this->formateValue($this->getResponseByName('lldp.locChassisId')->fetchOne()->getValue());
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
                    'name' => $this->formateValue($dt->getValue()),
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
                    'loc_interface' => $this->parseInterface($port),
                    'rem_chassis_id' => $this->formateValue($dt->getValue()),
                    'rem_interface' => null,
                    '_rem_port_id' => null,
                ];
            }
        }
        if(isset($this->response['lldp.remPortId'])) {
            if($error = $this->getResponseByName('lldp.remPortId')->error()) {
                throw new \SNMPException($error);
            }
            foreach ($this->getResponseByName('lldp.remPortId')->fetchAll() as $dt) {
                $id = Helper::getIndexByOid($dt->getOid());
                $port = Helper::getIndexByOid($dt->getOid(), 1);
                $remotes["{$port}.{$id}"]['loc_interface'] = $this->parseInterface($port);
                $remotes["{$port}.{$id}"]['rem_interface'] = $this->formateValue($dt->getValue());
                $remotes["{$port}.{$id}"]['_rem_port_id'] = $this->getPortId($remotes["{$port}.{$id}"]['rem_chassis_id'], $this->formateValue($dt->getValue()));
                if(!isset($remotes["{$port}.{$id}"]['rem_chassis_id'])) {
                    $remotes["{$port}.{$id}"]['rem_chassis_id'] = null;
                }

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
        try {
            return hexdec(str_replace(':', '', $portIdent)) - hexdec(str_replace(':', '', $chassisId));
        } catch (\Exception $e) {
            return null;
        }
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }

    function formateValue($value)
    {
        $value = trim($value);
        if(preg_match('/^([[:xdigit:]]{2}) ([[:xdigit:]]{2}) ([[:xdigit:]]{2}) ([[:xdigit:]]{2}) ([[:xdigit:]]{2}) ([[:xdigit:]]{2})$/', $value, $m)) {
            return "{$m[1]}:{$m[2]}:{$m[3]}:{$m[4]}:{$m[5]}:{$m[6]}";
        }
        return  $value;
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

