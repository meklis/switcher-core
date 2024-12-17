<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule
{
    use InterfacesTrait;

    public function run($params = [])
    {
        $oid = $this->oids->getOidByName('ip.arp.macAddr');
        $this->response = $this->formatResponse($this->snmp->walk([$oid]));
        return $this;
    }

    public function getPretty()
    {
        $ifaceByVlans = [];
        $vlanIds = [];
        foreach ($this->getInterfacesIds() as $interface) {
            $ifaceByVlans[$interface['id']] = $interface;
            if($interface['type'] == "BRIDGE" || $interface['type'] == "LACP") {
                $vlanIds[$interface['id']] = $interface['_port_num'];
            }
            if (isset($interface['_iface_vlans'])) {
                foreach ($interface['_iface_vlans'] as $key => $vlanId) {
                    $ifaceByVlans[$key] = $interface;
                    $vlanIds[$key] = $vlanId;
                }
            }
        }
        foreach ($ifaceByVlans as $iface => $ifaceInfo) {
            unset($ifaceInfo['_iface_vlans']);
            $ifaceByVlans[$iface] = $ifaceInfo;
        }

        if ($this->response['ip.arp.macAddr']->error()) {
            throw new \SNMPException($this->response['ip.arp.macAddr']->error());
        }
        $response = [];
        foreach ($this->response['ip.arp.macAddr']->fetchAll() as $mac) {
            $ip = Helper::getIndexByOid($mac->getOid(), 3) . "." .
                Helper::getIndexByOid($mac->getOid(), 2) . "." .
                Helper::getIndexByOid($mac->getOid(), 1) . "." .
                Helper::getIndexByOid($mac->getOid())
            ;
            $vlanIdIndex = Helper::getIndexByOid($mac->getOid(), 4);

            $response[] = [
                'mac' => $mac->getHexValue(),
                'ip' => $ip,
                'vlan_id' => isset($vlanIds[$vlanIdIndex]) ? $vlanIds[$vlanIdIndex] : null,
                'interface' => isset($ifaceByVlans[$vlanIdIndex]) ? $ifaceByVlans[$vlanIdIndex] : null,
                '_raw_iface_id' => $vlanIdIndex,
            ];
        }

        return $response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}