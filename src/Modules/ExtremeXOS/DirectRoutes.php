<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SwitcherCore\Modules\AbstractModule;

class DirectRoutes extends AbstractModule {

    public function run($params = []) {
        $input = $this->getModule('multi_console_command')
            ->run(['commands' => [
                'sh iproute origin direct',
                'sh vlan',
            ]])->getPretty();
        $exploded = explode("\n", $input[0]['output']);

        $response = [];
        foreach($exploded as $line) {
            if(substr($line, 0, 3) === 'Ori') continue;
            if($line === '') break;

            if(preg_match('/^.?d\s+(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2})\s+(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s+.*?\s+.*?\s+([A-Za-z0-9\._\-]+)\s+.+$/', $line, $m)) {
                $cidr = $m[1];
                $gateway = $m[2];
                $vlan_name = $m[3];
                $expl = explode('/', $cidr);
                $network = $expl[0];
                $netmask = long2ip(0xffffffff << (32 - $expl[1]));
                $bcast = long2ip(ip2long($gateway) | ip2long( long2ip( ~ip2long($netmask) ) ));
                $response[] = [
                    'type' => 'v4',
                    'network' => $network,
                    'broadcast' => $bcast,
                    'gateway' => $gateway,
                    'cidr' => $cidr,
                    'vlan_name' => $vlan_name
                ];
            }
        }

        $exploded = explode("\n", $input[1]['output']);
        $vlans = [];
        foreach($exploded as $line) {
            if(preg_match('/^([A-Za-z0-9\._\-]+)\s+(\d{1,4})\s+.+$/', $line, $m)) {
                $vlans[$m[1]] = $m[2];
            }
        }
        foreach($response as $i => $arr) {
            if(!isset($vlans[$arr['vlan_name']])) {
                unset($response[$i]);
                continue;
            }
            $response[$i]['vlan_id'] = $vlans[$arr['vlan_name']];
        }
        $this->response = array_values($response);
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }
}