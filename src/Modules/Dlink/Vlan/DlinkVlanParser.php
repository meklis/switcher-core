<?php


namespace SwitcherCore\Modules\Dlink\Vlan;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;

class DlinkVlanParser extends SwitchesPortAbstractModule
{
    protected function formate() {
        $response = [];
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
        $indexes = $this->getIndexes();
        $formater = function ($resp) use ($indexes) {
            $dex = Helper::hexToBinStr($resp->getHexValue());
            $ports = [];
            for($port = 1; $port <= strlen($dex) ; $port++) {
                if($dex[$port-1] == '1' && isset($indexes[$port])) $ports[] = $indexes[$port];
            }
            return $ports;
        };

        $response = [];
        foreach ($names->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['name'] = $resp->getValue();
            $response[Helper::getIndexByOid($resp->getOid())]['id'] = Helper::getIndexByOid($resp->getOid());
        }
        foreach ($egress->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['egress'] = $formater($resp);
        }
        foreach ($untagged->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['untagged'] = $formater($resp);
        }
        foreach ($response as $vlan_id => $resp) {
            foreach ($resp['ports']['egress'] as $port) {
                if(isset($response[$vlan_id]['ports']['untagged']) && in_array($port, $response[$vlan_id]['ports']['untagged'])) continue;
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

        if($filter['vlan_id']) {
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$filter['vlan_id']}";
            }
        }
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walkNext($oidObjects));
        return $this;
    }
}
