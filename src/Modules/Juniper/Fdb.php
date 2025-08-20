<?php

namespace SwitcherCore\Modules\Juniper;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\FdbDot1BridgeWithConsole;
use SwitcherCore\Modules\Juniper\InterfacesTrait;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1BridgeWithConsole {
    use InterfacesTrait;

    protected function getFromSNMP() {
        $oids[] = Oid::init($this->oids->getOidByName('jnx.vlan.tag')->getOid());
        $oids[] = Oid::init($this->oids->getOidByName('jnx.vlan.fdb_id')->getOid());
        $oids[] = Oid::init($this->oids->getOidByName('dot1q.FdbStatus')->getOid());
        $oids[] = Oid::init($this->oids->getOidByName('dot1q.FdbPort')->getOid());
        $res = $this->formatResponse($this->snmp->walk($oids));
        if($res['jnx.vlan.tag']->error()) {
            throw new \Exception("Returned error {$res['jnx.vlan.tag']->error()} from {$res['jnx.vlan.tag']->getRaw()->ip}");
        }
        if($res['jnx.vlan.fdb_id']->error()) {
            throw new \Exception("Returned error {$res['jnx.vlan.fdb_id']->error()} from {$res['jnx.vlan.fdb_id']->getRaw()->ip}");
        }
        if($res['dot1q.FdbStatus']->error()) {
            throw new \Exception("Returned error {$res['dot1q.FdbStatus']->error()} from {$res['dot1q.FdbStatus']->getRaw()->ip}");
        }
        if($res['dot1q.FdbPort']->error()) {
            throw new \Exception("Returned error {$res['dot1q.FdbPort']->error()} from {$res['dot1q.FdbPort']->getRaw()->ip}");
        }

        $premap = [];
        foreach ($res['jnx.vlan.fdb_id']->fetchAll() as $val) {
            $id = Helper::getIndexByOid($val->getOid());
            $premap[$id] = (int)$val->getValue();
        }
        $vlanIdAssoc = [];
        foreach ($res['jnx.vlan.tag']->fetchAll() as $val) {
            $id = Helper::getIndexByOid($val->getOid());
            $vlanIdAssoc[$premap[$id]] = $val->getValue();
        }

        while ($d = $res['dot1q.FdbStatus']->fetchOne()) {
            $data = Helper::oid2MacVlan($d->getOid());
            $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
        }
        while ($d = $res['dot1q.FdbPort']->fetchOne()) {
            $data = Helper::oid2MacVlan($d->getOid());
            $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
        }

        $result = [];
        foreach ($statuses as $key => $status) {
            list($vlanId, $macAddr) = explode("-", $key);
            if(!isset($ports[$key])) {
                continue;
            }
            if(!(int)$ports[$key])  continue;
            try {
                $result[] = [
                    'interface' => $this->parseInterface($ports[$key], '_dot1q_id'),
                    'vlan_id' => isset($vlanIdAssoc[$vlanId]) ? (int)$vlanIdAssoc[$vlanId] : null,
                    'mac_address' => $macAddr,
                    'status' => $status,
                ];
            } catch (\Throwable $e) {}
        }

        return $result;
    }

    protected function getFromConsole(array $filter) {
        $cmd = 'show mac-vrf forwarding mac-table ';
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface'])['name'];
            $cmd .= 'interface ' . $iface;
        } else if ($filter['mac']) {
            $mac = Helper::formatMac($filter['mac']);
            $cmd .= $mac;
        }
        if($filter['vlan_id']) $cmd .= ' vlan-id ' . $filter['vlan_id'];
        $cmd .= ' | display json ';

        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}");
        $res = json_decode($res['output'], true);

        $response = [];
        foreach($res['l2ng-l2ald-rtb-macdb']['l2ng-l2ald-mac-entry-vlan']['l2ng-mac-entry'] as $arr) {
            $mac = Helper::formatMac($arr['l2ng-l2-mac-address']);
            $status = (($arr['l2ng-l2-mac-flags'] === 'C') ? 'SYSTEM' : (($arr['l2ng-l2-mac-flags'] === 'S' || $arr['l2ng-l2-mac-flags'] === 'P') ? 'STATIC' : 'LEARNED'));
            $iface = $arr['l2ng-l2-mac-logical-interface'];
            $pos = strpos($iface, '.');
            if($pos === false) {
                $vlan = 0;
            } else {
                $vlan = substr($iface, $pos + 1);
                $iface = substr($iface, 0, $pos);
            }
            $interface = $this->parseInterface($iface, 'name');

            $response[] = [
                'interface' => $interface,
                'vlan_id' => $vlan,
                'mac_address' => $mac,
                'status' => $status,
            ];
        }

        return $response;
    }

}
