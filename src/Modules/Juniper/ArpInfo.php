<?php

namespace SwitcherCore\Modules\Juniper;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule {
    use InterfacesTrait;

    public function run($params = []) {
        $filter_vlan = false;
        $filter_ip = false;
        $filter_mac = false;
        if(isset($params['vlan_id']) && intval($params['vlan_id']) > 0 && intval($params['vlan_id']) < 4095) {
            $filter_vlan = $params['vlan_id'];
        }
        if(isset($params['ip']) && preg_match('/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/', $params['ip'])) {
            $filter_ip = $params['ip'];
        }
        if(isset($params['mac'])) {
            $filter_mac = Helper::formatMac($params['mac']);
        }

        foreach ($this->getInterfacesIds() as $interface) {
            $ifaceByVlans[$interface['id']] = $interface;
            if($interface['type'] == "BRIDGE" || $interface['type'] == "LACP") {
                $vlanIds[$interface['id']] = $interface['_port_num'];
                if($filter_vlan && $interface['_port_num'] == $filter_vlan) {
                    $filter_vlan_index = $interface['id'];
                }
            }
            if (isset($interface['_iface_vlans'])) {
                foreach ($interface['_iface_vlans'] as $key => $vlanId) {
                    $ifaceByVlans[$key] = $interface;
                    $vlanIds[$key] = $vlanId;
                    if($filter_vlan && $vlanId == $filter_vlan) {
                        $filter_vlan_index = $key;
                    }
                }
            }
        }
        foreach ($ifaceByVlans as $iface => $ifaceInfo) {
            unset($ifaceInfo['_iface_vlans']);
            $ifaceByVlans[$iface] = $ifaceInfo;
        }
        $oid = $this->oids->getOidByName('ip.arp.macAddr')->getOid();
        if($filter_vlan && isset($filter_vlan_index)) {
            $oid .= ".{$filter_vlan_index}";
        }
        if($filter_vlan && isset($filter_vlan_index) && $filter_ip) {
            $oid .= ".{$filter_ip}";
        }
        $res = $this->formatResponse($this->snmp->walk([Oid::init($oid)]));

        $this->response = [];
        foreach($res['ip.arp.macAddr']->fetchAll() as $val) {
            $ip = Helper::oid2IP($val->getOid());
            if($filter_ip && $ip !== $filter_ip) continue;
            $vlan_index = Helper::getIndexByOid($val->getOid(), 4);
            $mac = $val->getHexValue();
            if($filter_mac && $mac !== $filter_mac) continue;

            $this->response[] = [
                'interface' => isset($ifaceByVlans[$vlan_index]) ? $ifaceByVlans[$vlan_index] : null,
                'mac' => $mac,
                'ip' => $ip,
                'vlan_id' => isset($vlanIds[$vlan_index]) ? $vlanIds[$vlan_index] : null,
                '_raw_iface_id' => $vlan_index,
            ];

        }
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->getPretty();
    }

}