<?php


namespace SwitcherCore\Modules\RouterOS;

class DirectRoutes extends ExecCommand {
    function getPretty() {
        return $this->response;
    }

    function getPrettyFiltered($filter = []) {
        return $this->response;
    }

    public function run($params = []) {
        $res = $this->execComm('/ip/address/print', ['?disabled' => 'no']);
        time_nanosleep(0, 100000000);
        $res_vlans = $this->execComm("/interface/vlan/print");
        $vlans = [];
        foreach($res_vlans as $vlan) {
            $vlans[$vlan['name']] = $vlan['vlan-id'];
        }
        $this->response = [];
        foreach($res as $d) {
            $expl = explode('/', $d['address']);
            $gateway = $expl[0];
            $netmask = long2ip(0xffffffff << (32 - $expl[1]));
            $bcast = long2ip(ip2long($gateway) | ip2long( long2ip( ~ip2long($netmask) ) ));
            $cidr = sprintf("%s/%s", $d['network'], $expl[1]);
            
            $this->response[] = [
                'type' => 'v4',
                'network' => $d['network'],
                'broadcast' => $bcast,
                'gateway' => $gateway,
                'cidr' => $cidr,
                'vlan_id' => ((isset($vlans[$d['interface']])) ? $vlans[$d['interface']] : 0),
                'vlan_name' => $d['interface'],
            ];
        }

        time_nanosleep(0, 100000000);
        $res = $this->execComm('/ipv6/address/print', ['?disabled' => 'no']);
        foreach($res as $d) {
                $expl = explode('/', $d['address']);
                $firstaddr = $expl[0];
                $firstaddrbin = inet_pton($firstaddr);
                $unpack = unpack('H*', $firstaddrbin);
                $firstaddrhex = reset($unpack);
                $firstaddr = inet_ntop($firstaddrbin);
                $flexbits = 128 - $expl[1];
                $lastaddrhex = $firstaddrhex;
                $pos = 31;
                while ($flexbits > 0) {
                    $orig = substr($lastaddrhex, $pos, 1);
                    $origval = hexdec($orig);
                    $newval = $origval | (pow(2, min(4, $flexbits)) - 1);
                    $new = dechex($newval);
                    $lastaddrhex = substr_replace($lastaddrhex, $new, $pos, 1);
                    $flexbits -= 4;
		            $pos -= 1;
                }
                $lastaddrbin = pack('H*', $lastaddrhex);
	            $lastaddr = inet_ntop($lastaddrbin);
            $this->response[] = [
                'type' => 'v6',
                'network' => $firstaddr,
                'broadcast' => $lastaddr,
                'cidr' => $d['address'],
                'vlan_id' => isset($vlans[$d['actual-interface']]) ? $vlans[$d['actual-interface']] : 0,
                'vlan_name' => $d['actual-interface'],
                'global' => (isset($d['global']) && $d['global']),
            ]; 
        }

        return $this;
    }

}