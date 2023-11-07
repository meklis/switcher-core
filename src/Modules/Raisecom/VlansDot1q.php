<?php

namespace SwitcherCore\Modules\Raisecom;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class VlansDot1q extends \SwitcherCore\Modules\General\Switches\VlansDot1q
{
    use InterfacesTrait;
    protected function formate() {
        $response = [];
        $forbidden = $this->getResponseByName('dot1q.VlanStaticForbiddenEgressPorts');
        $untagged = $this->getResponseByName('dot1q.VlanStaticUntaggedPorts');
        $names = $this->getResponseByName('dot1q.VlanStaticName');
        $egress = $this->getResponseByName('dot1q.VlanStaticEgressPorts');

        if($names->error()) {
            throw new IncompleteResponseException($names->error());
        }
        if($egress->error()) {
            throw new IncompleteResponseException($egress->error());
        }
        if($untagged->error()) {
            throw new IncompleteResponseException($untagged->error());
        }
        if($forbidden->error()) {
            throw new IncompleteResponseException($forbidden->error());
        }


        $indexes = [];
        foreach ($this->getInterfacesIds() as $iface) {
            $indexes[$iface['_port']] = $iface;
        }
        $formater = function ($resp) use ($indexes) {
            $dex = str_split(Helper::hexToBinStr($resp->getHexValue()), 32);
            $ports = array_map(function ($e) {
                return bindec($e);
            }, $dex);
            return array_values(array_filter($ports, function ($e) {
              return $e > 10;
            }));
        };

        $response = [];
        foreach ($names->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['name'] = $resp->getValue();
            $response[Helper::getIndexByOid($resp->getOid())]['id'] = Helper::getIndexByOid($resp->getOid());
        }

        foreach ($untagged->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['untagged'] = $formater($resp);
        }
        foreach ($egress->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['egress'] = $formater($resp);
        }
        foreach ($forbidden->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['forbidden'] = $formater($resp);
        }
        foreach ($response as $vlan_id => $resp) {
            foreach ($resp['ports']['egress'] as $port) {
                if(in_array($port, $response[$vlan_id]['ports']['untagged'])) continue;
                if(in_array($port, $response[$vlan_id]['ports']['forbidden'])) continue;
                $response[$vlan_id]['ports']['tagged'][] = $port;
            }
        }

        return array_values($response);
    }
    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$val) {
                if($interface['id'] != $val['interface']['id']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $oids[] = $this->oids->getOidByName('dot1q.VlanStaticName')->getOid();
        $oids[] = $this->oids->getOidByName('dot1q.VlanStaticEgressPorts')->getOid();
        $oids[] = $this->oids->getOidByName('dot1q.VlanStaticForbiddenEgressPorts')->getOid();
        $oids[] = $this->oids->getOidByName('dot1q.VlanStaticUntaggedPorts')->getOid();
        $oids[] = $this->oids->getOidByName('dot1q.VlanStaticRowStatus')->getOid();

        if($filter['vlan_id']) {
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$filter['vlan_id']}";
            }
        }
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
