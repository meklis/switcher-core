<?php

namespace SwitcherCore\Modules\Arista;

use \Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Arista\InterfacesTrait;
use SwitcherCore\Modules\General\FdbDot1BridgeWithConsole;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1BridgeWithConsole {
    use InterfacesTrait;

    protected function getFromSNMP() {
        $oids = [
            Oid::init($this->oids->getOidByName('dot1q.PortIfIndex')->getOid()),
            Oid::init($this->oids->getOidByName('dot1q.FdbPort')->getOid()),
            Oid::init($this->oids->getOidByName('dot1q.FdbStatus')->getOid()),
        ];
        $res = $this->formatResponse($this->snmp->walk($oids));
        if($res['dot1q.PortIfIndex']->error()) {
            throw new \Exception("Returned error {$res['dot1q.PortIfIndex']->error()} from {$res['dot1q.PortIfIndex']->getRaw()->ip}");
        }
        if($res['dot1q.FdbPort']->error()) {
            throw new \Exception("Returned error {$res['dot1q.FdbPort']->error()} from {$res['dot1q.FdbPort']->getRaw()->ip}");
        }
        if($res['dot1q.FdbStatus']->error()) {
            throw new \Exception("Returned error {$res['dot1q.FdbStatus']->error()} from {$res['dot1q.FdbStatus']->getRaw()->ip}");
        }

        $statuses = [];
        $ports = [];

        while ($d = $res['dot1q.PortIfIndex']->fetchOne()) {
            $index = Helper::getIndexByOid($d->getOid());
            $if_indexes[$index] = $d->getParsedValue();
        }
        while ($d = $res['dot1q.FdbStatus']->fetchOne()) {
            $data = Helper::oid2MacVlan($d->getOid());
            $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
        }    
        while ($d = $res['dot1q.FdbPort']->fetchOne()) {
            $data = Helper::oid2MacVlan($d->getOid());
            $port = isset($if_indexes[$d->getValue()]) ? $if_indexes[$d->getValue()] : $d->getValue();
            $ports["{$data['vid']}-{$data['mac']}"] = $port;
        }

        $response = [];
        foreach ($statuses as $key => $status) {
            list($vlanId, $macAddr) = explode("-", $key);
            if(!isset($ports[$key])) continue;
            if(!(int)$ports[$key]) continue;
            try {
                $response[] = [
                    'interface' => $this->parseInterface($ports[$key], 'physical_id'),
                    'vlan_id' => (int)$vlanId,
                    'mac_address' => $macAddr,
                    'status' => $status,
                ];
            } catch (\Throwable $e) {}
        }

        return array_values($response);
    }

    protected function getFromConsole(array $filter) {
        $cmd = 'show mac address-table';
        if($filter['mac']) {
            $mac = Helper::formatMac3Blocks($filter['mac']);
            $cmd .= ' address ';
            $cmd .= $mac;
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $cmd .= ' interface ';
            $cmd .= $iface['name'];
        }
        if($filter['vlan_id']) {
            $cmd .= ' vlan ';
            $cmd .= $filter['vlan_id'];
        }
        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}");
        $res = explode("\n", $res['output']);
        $response = [];
        foreach($res as $i => $str) {
            if($i < 5) continue;
            if(strpos(trim($str), 'Total Mac Addresses for this criterion:') !== false) break;
            if(preg_match('/(\d{1,4})\s+(....\.....\.....)\s+([A-Z]{4,})\s+(Et\d{1,2}(\/\d)?|Po\d)/i', trim($str), $m)) {
                $vlan_id = $m[1];
                $mac = Helper::formatMac($m[2]);
                $status = ($m[3] === 'DYNAMIC') ? 'LEARNED' : 'STATIC';
                if(preg_match('/^Po(\d)$/i', $m[4], $mm)) {
                    $iface = 'Port-Channel' . $mm[1];
                } elseif(preg_match('/^Et(\d{1,2}\/?\d?)$/i', $m[4], $mm)) {
                    $iface = 'Ethernet' . $mm[1];
                } else {
                    continue;
                }
                $interface = $this->parseInterface($iface, 'name');
                
                $response[] = [
                    'interface' => $interface,
                    'vlan_id' => $vlan_id,
                    'mac_address' => $mac,
                    'status' => $status,
                ];
            }
        }

        return array_values($response);
    }
}
