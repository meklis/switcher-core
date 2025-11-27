<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SwitcherCore\Modules\Helper;

class SnoopingInfo extends ModuleAbstract {
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        $data = $this->getPretty();
        if(count($data) === 0) return [];
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac_address']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac_address']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        if ($filter['ip']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['ip'] == $filter['ip'];
            });
        }
        return array_values($data);
    }

    function getPretty() {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = []) {
        if(isset($filter['ip'])) {
            $cmd = 'show ip dhcp snooping dynamic ip ' . $filter['ip'];
            $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
            $r = explode("\n", $r['output']);
            if(count($r) < 2) throw new Exception("SnoopingInfo: {$filter['ip']} not found");
            $resp = [];
            foreach($r as $line) {
                if(preg_match('/^MAC addr\s+:((([0-9a-f]{4})\.?){3})$/i', trim($line), $m)) {
                    $resp['mac_address'] = Helper::formatMac($m[1]);
                }
                if(preg_match('/^IP addr\s+:(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})$/i', trim($line), $m)) {
                    $resp['ip'] = $m[1];
                }
                if(preg_match('/^VLAN\s+:(\d{1,4})$/i', trim($line), $m)) {
                    $resp['vlan_id'] = (int) $m[1];
                }
                if(preg_match('/^Interface\s+:((\w+)[-_]?(\w+)?[-_]?\d\/\d{1,3}(\/\d{1,3})?(([:\.])?(\d{1,3})?)?(([:\.])?(\d{1,3})?)?)/i', trim($line), $m)) {
                    $resp['interface'] = $this->parseInterface($m[1]);
                }
                if(preg_match('/^Remaining\s+:((\d{1,3}):(\d{2}):(\d{2}))/i', trim($line), $m)) {
                    $hours = (int) $m[2];
                    $minutes = (int) $m[3];
                    $seconds = (int) $m[4];
                    $resp['remaining'] = $seconds + $minutes * 60 + $hours * 60 * 60;
                }
                if(preg_match('/^Server IP\s+:(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})$/i', trim($line), $m)) {
                    $resp['_server_ip'] = $m[1];
                }
                if(preg_match('/^Gateway IP\s+:(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})$/i', trim($line), $m)) {
                    $resp['_gateway_ip'] = $m[1];
                }
                if(preg_match('/^Subnet mask\s+:(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})$/i', trim($line), $m)) {
                    $resp['_subnet_mask'] = $m[1];
                }
            }
            $this->response = [$resp];
            return $this;
        }

        $ifaces_4mac = [];
        if(isset($filter['mac_address'])) {
            $cmd = 'show mac ' . Helper::formatMac3Blocks($filter['mac_address']);
            $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
            $r = explode("\n", $r['output']);
            foreach($r as $line) {
                if(preg_match('/((([0-9a-f]{4})\.?){3})\s+(\d{1,4})\s+(dynamic|static)\s+((\w+)[-_]?(\w+)?[-_]?\d\/\d{1,3}(\/\d{1,3})?(([:\.])?(\d{1,3})){0,2})/i', trim($line), $m)) {
                    $ifaces_4mac[$m[6]] = $m[6];
                }
            }
        }
        if(isset($filter['vlan_id'])) {
            $cmd = "show mac vlan {$filter['vlan_id']}";
            $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
            $r = explode("\n", $r['output']);
            foreach($r as $line) {
                if(preg_match('/((([0-9a-f]{4})\.?){3})\s+(\d{1,4})\s+(dynamic|static)\s+((\w+)[-_]?(\w+)?[-_]?\d\/\d{1,3}(\/\d{1,3})?(([:\.])?(\d{1,3})){0,2})/i', trim($line), $m)) {
                    $ifaces_4mac[$m[6]] = $m[6];
                }
            }
        }
        if(isset($filter['interface'])) {
            $iface = $this->parseInterface($filter['interface'])['name'];
            // $iface = str_replace(['gpon_onu-', 'epon_onu-'], 'vport-', $iface);
            // $iface = str_replace(':', '.', $iface);
            // $iface .= ':1';
            $iface = "vport-{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}.{$iface['_onu']}:1";
            $ifaces_4mac = [$iface];
        }
        

        $out = [];
        if(count($ifaces_4mac) > 0) {
            foreach($ifaces_4mac as $iface) {
                $cmd = "show ip dhcp snooping dynamic port $iface";
                $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
                $r = explode("\n", $r['output']);
                $out = array_merge($r, $out);
            }
        } else {
            $cmd = "show ip dhcp snooping dynamic database";
            $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
            $r = explode("\n", $r['output']);
            $out = $r;
        }

        $resp = [];
        $add_next_line = false;
        foreach($out as $line) {
            if($add_next_line) {
                if(preg_match('/^(\/\d{1,3}([:\.]\d{1,3})?([:\.]\d{1,3})?)/', trim($line), $m)) {
                    $interface .= $m[1];
                    $resp[] = [
                        'interface' => $this->parseInterface($interface),
                        'mac_address' => $mac,
                        'ip' => $ip,
                        'vlan_id' => $vlan_id,
                        'remaining' => $remaining,
                    ];
                }
                $add_next_line = false;
                continue;
            }
            if(preg_match('/^\d{1,5}\s+(([0-9a-f]{4}\.?){3})\s+(((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4})\s+(\d{1,4})\s+dynamic\s+(\w+[-_]\d\/\d{1,3})\s+(\d{1,3}:\d{2}:\d{2})\s+\d?\s*((\d{1,3}):(\d{2}):(\d{2}))/i', trim($line), $m)) {
                $mac = Helper::formatMac($m[1]);
                $ip = $m[3];
                $vlan_id = (int) $m[7];
                $interface = $m[8];
                $hours = (int) $m[11];
                $minutes = (int) $m[12];
                $seconds = (int) $m[13];
                $remaining = $seconds + $minutes * 60 + $hours * 60 * 60;
                $add_next_line = true;
            }
        }

        $this->response = $resp;
        return $this;
    }
}

