<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule {
    use InterfacesTrait;

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }

    public function getPretty() {
        return $this->response;
    }

    public function run($params = []) {
        $cmd = 'show arp';
        if(isset($params['vlan_id']) && intval($params['vlan_id']) > 0 && intval($params['vlan_id']) < 4095) {
            $cmd .= ' interface vlan' . $params['vlan_id'];
        }
        if(isset($params['ip']) && preg_match('/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/', $params['ip'])) {
            $cmd .= ' ' . $params['ip'];
        }
        if(isset($params['mac'])) {
            $mac = Helper::formatMac3Blocks($params['mac']);
            $cmd .= ' mac-address ' . $mac;
        }
        $cmd .= " | json";
        
        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}");

        $this->response = [];
        $res = json_decode($res['output'], true);
        if(isset($res['ipV4Neighbors'])) {
            foreach($res['ipV4Neighbors'] as $row) {
                $vlan_id = null;
                if(preg_match('/vlan(\d{1,4})/i', $row['interface'], $m)) {
                    $vlan_id = $m[1];
                }
                $iface = null;
                if(preg_match('/(Ethernet\d{1,3}\/?\d?\d?\d?|Port\-Channel\d{1,3})/', $row['interface'], $m)) {
                    $iface = $this->parseInterface($m[1], 'name');
                }
                $this->response[] = [
                    'interface' => $iface,
                    'mac' => Helper::formatMac($row['hwAddress']),
                    'ip' => $row['address'],
                    'vlan_id' => $vlan_id,
                    '_age' => (isset($row['age']) ? $row['age'] : null),
                ];
            }
        }
        return $this;
    }
}