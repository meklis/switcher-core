<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule {
    use InterfacesTrait;

    public function run($params = []) {
        $cmd = 'show ip arp';
        if (isset($params['mac'])) {
            $mac = Helper::formatMac3Blocks($params['mac']);
            $cmd .= ' mac-address ' . $mac;
        } elseif (isset($params['ip']) && preg_match('/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/', $params['ip'])) {
            $cmd .= ' ' . $params['ip'];
        } elseif (isset($params['vlan_id']) && intval($params['vlan_id']) > 0 && intval($params['vlan_id']) < 4095) {
            $cmd .= ' interface vlan' . $params['vlan_id'];
        }
        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}. output = {$res['output']}");
        $exploded = explode("\n", $res['output']);
        $this->response = [];
        foreach($exploded as $row) {
            if(preg_match('/^(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})\s+(([a-f0-9]{2}\:){5}[a-f0-9]{2})\s+vlan(\d{1,4})\s+(port\-channel\d{1,3}|ethernet\d{1,3}\/\d{1,3}\/\d{1,3}\:?\d?\d?\d?)/i', $row, $m)) {
                $this->response[] = [
                    'interface' => $this->parseInterface($m[8], 'name'),
                    'mac' => Helper::formatMac($m[5]),
                    'ip' => $m[1],
                    'vlan_id' => $m[7],
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