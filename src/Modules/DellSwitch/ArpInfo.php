<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule {
    use InterfacesTrait;

    public function run($params = []) {
        $cmd = 'show arp';
        if (isset($params['mac'])) {
            $mac = Helper::formatMac($params['mac']);
            $cmd .= ' macaddress ' . $mac;
        } elseif (isset($params['ip']) && preg_match('/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/', $params['ip'])) {
            $cmd .= ' ip ' . $params['ip'];
        } elseif (isset($params['vlan_id']) && intval($params['vlan_id']) > 0 && intval($params['vlan_id']) < 4095) {
            $cmd .= ' interface vlan ' . $params['vlan_id'];
        }
        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}. output = {$res['output']}");
        $exploded = explode("\n", $res['output']);
        $this->response = [];
        foreach($exploded as $row) {
            if(preg_match('/^.*\s(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})\s+(\d{1,6}|\-)\s+(([a-f0-9]{2}\:){5}[a-f0-9]{2})\s+(\-|.*)\s+Vl\s(\d{1,4})/i', $row, $m)) {
                $iface = null;
                if(preg_match('/^Po\s(\d{1,3})$/', trim($m[8]), $mm)) {
                    $iface = $this->parseInterface('Port-channel ' . $mm[1], 'name');
                } elseif(preg_match('/^Te\s(\d\/\d+)$/', trim($m[8]), $mm)) {
                    $iface = $this->parseInterface('TenGigabitEthernet ' . $mm[1], 'name');
                } elseif(preg_match('/^Fo\s(\d\/\d+)$/', trim($m[8]), $mm)) {
                    $iface = $this->parseInterface('fortyGigE ' . $mm[1], 'name');
                }
                $this->response[] = [
                    'interface' => $iface,
                    'mac' => Helper::formatMac($m[6]),
                    'ip' => $m[1],
                    'vlan_id' => $m[9],
                    '_age' => (($m[5] === '-') ? null : $m[5]),
                ];
            }
        }
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}