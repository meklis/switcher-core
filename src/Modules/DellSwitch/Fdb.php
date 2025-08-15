<?php

namespace SwitcherCore\Modules\DellSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\DellSwitch\InterfacesTrait;
use SwitcherCore\Modules\General\FdbDot1BridgeWithConsole;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1BridgeWithConsole {
    use InterfacesTrait;

    protected function getFromSNMP() {
        $oid = $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        $res = $this->formatResponse($this->snmp->walkBulk([ Oid::init($oid) ]));
        if($res['dot1q.FdbPort']->error()) {
            throw new \Exception("Returned error {$res['dot1q.FdbPort']->error()} from {$res['dot1q.FdbPort']->getRaw()->ip}");
        }
        $response = [];
        while ($d = $res['dot1q.FdbPort']->fetchOne()) {
            $data = Helper::oid2MacVlan($d->getOid());
            try {
                $iface = $this->parseInterface($d->getParsedValue(), '_dot1q_id');
                if(!$iface) continue;
                $response[] = [
                    'interface' => $iface,
                    'vlan_id' => (int) $data['vid'],
                    'mac_address' => $data['mac'],
                    'status' => null,
                ];
            } catch(\Throwable $e) {}
        }
        return array_values($response);
    }

    protected function getFromConsole(array $filter) {
        $cmd = 'show mac-address-table';
        if($filter['mac']) {
            $mac = Helper::formatMac($filter['mac']);
            $cmd .= ' address ';
            $cmd .= $mac;
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $cmd .= ' interface ';
            $cmd .= $iface['name'];
        }
        if($filter['vlan_id']) $cmd .= ' vlan ' . $filter['vlan_id'];

        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}");
        $res = explode("\n", $res['output']);

        $response = [];
        foreach($res as $i => $str) {
            if($i < 4) continue;
            if(preg_match('/(\d{1,4})\s+(..:..:..:..:..:..)\s+([A-Z]{4,})\s+(Fo\s\d\/\d+|Te\s\d\/\d+|Po\s\d{1,3})/i', trim($str), $m)) {
                $vlan_id = $m[1];
                $mac = Helper::formatMac($m[2]);
                $status = (strtoupper($m[3]) === 'DYNAMIC') ? 'LEARNED' : 'STATIC';

                if(preg_match('/^Po\s(\d{1,3})$/i', $m[4], $mm)) {
                    $iface = 'Port-channel ' . $mm[1];
                } elseif(preg_match('/^Te\s(\d\/\d+)$/i', $m[4], $mm)) {
                    $iface = 'TenGigabitEthernet ' . $mm[1];
                } elseif(preg_match('/^Fo\s(\d\/\d+)$/i', $m[4], $mm)) {
                    $iface = 'fortyGigE ' . $mm[1];
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
