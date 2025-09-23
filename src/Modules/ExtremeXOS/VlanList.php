<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Helper;

class VlanList extends \SwitcherCore\Modules\General\Switches\VlansDot1q {
    use InterfacesTrait;

    private $filter_vlan_id = false;

    protected function formate() {
        $vlan_ids = $this->getResponseByName('extreme.vlanIfVlanId');
        $names = $this->getResponseByName('extreme.vlanIfDescr');
        $tagged = $this->getResponseByName('extreme.vlanOpaqueTaggedPorts');
        $untagged = $this->getResponseByName('extreme.vlanOpaqueUntaggedPorts');
        if($vlan_ids->error()) throw new IncompleteResponseException($vlan_ids->error());
        if($names->error()) throw new IncompleteResponseException($names->error());
        if($untagged->error()) throw new IncompleteResponseException($untagged->error());
        if($tagged->error()) throw new IncompleteResponseException($tagged->error());

        $indexes = [];
        foreach ($this->getInterfacesIds() as $iface) {
            $indexes[$iface['_dot1q_id']] = $iface;
        }
        
        $formater = function ($resp) use ($indexes) {
            $dex = Helper::hexToBinStr($resp->getHexValue());
            $ports = [];
            for($port = 1; $port < strlen($dex) ; $port++) {
                if($dex[$port] == '1' && isset($indexes[$port])) $ports[] = $indexes[$port];
            }
            return $ports;
        };

        $response = [];
        foreach ($names->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['name'] = $resp->getValue();
        }
        foreach ($vlan_ids->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['id'] = $resp->getValue();
        }

        foreach ($tagged->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid(), 1)]['ports']['tagged'] = $formater($resp);
        }
        foreach ($untagged->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid(), 1)]['ports']['untagged'] = $formater($resp);
        }
        foreach ($response as $vlan_if_id => $resp) {
            $response[$vlan_if_id]['ports']['egress'] = [];
            $response[$vlan_if_id]['ports']['forbidden'] = [];
            if(!isset($resp['ports']['tagged'])) $response[$vlan_if_id]['ports']['tagged'] = [];
            if(!isset($resp['ports']['untagged'])) $response[$vlan_if_id]['ports']['untagged'] = [];
            if($this->filter_vlan_id && $this->filter_vlan_id != $resp['id']) unset($response[$vlan_if_id]);
        }
        return array_values($response);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        return $this->getPretty();
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        if($filter['vlan_id']) $this->filter_vlan_id = $filter['vlan_id'];
        $oids[] = $this->oids->getOidByName('extreme.vlanIfVlanId')->getOid();
        $oids[] = $this->oids->getOidByName('extreme.vlanIfDescr')->getOid();
        $oids[] = $this->oids->getOidByName('extreme.vlanOpaqueTaggedPorts')->getOid();
        $oids[] = $this->oids->getOidByName('extreme.vlanOpaqueUntaggedPorts')->getOid();
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }

}
