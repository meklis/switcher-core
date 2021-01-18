<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class OntInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parsePortByName($params['onu']);
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getInfoGPON($params['onu']); break;
            case 'epon': $this->response = $this->getInfoEPON($params['onu']); break;
            default: throw new \Exception("Unknown type of interface - '$type'");
        }
        return $this;
    }

    private function getInfoEPON($interface)
    {
        $input = $this->obj->telnet->exec("show epon onu state {$interface}");
        if (!$input) throw new \Exception("Empty response on command 'show epon onu state {$interface}'");
        $lines = explode("\n", $input);
        $response = [];
        foreach (array_splice($lines, 2) as $line) {
            if(preg_match('/^(.*)[ ]{1,}(Power Off|Online)[ ]{1,}(idle|complete)[ ]{1,}(.*)$/', trim($line), $matches)) {
                $response[] = [
                    'interface' => trim($matches[1]),
                    'online_status' => trim($matches[2]),
                    'oam_status' => trim($matches[3]),
                    'mac' => trim($matches[4]),
                ];
            }
        }
        return [
            'data' => $response,
            'type' => 'epon',
        ];
    }

    private function getInfoGPON($interface)
    {
        //Get gereral information and logs
        $input = $this->obj->telnet->exec("show gpon onu detail-info {$interface}");
        if (!$input) throw new \Exception("Empty response on command ' gpon onu detail-info {$interface}'");
        if(preg_match('/No related information to show/', $input)) {
            throw new \Exception('No related information to show');
        }
        @list($info, $logs) = @explode("------------------------------------------", $input);
        if(!$logs || !$info) {
            throw new \Exception("Error parse ont information");
        }
        $lines = explode("\n", $info);
        $ont_info = [];
        foreach ($lines as $line) {
            if(preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $val = trim($m[2]);
                switch (trim($m[1])) {
                    case 'Name': $ont_info['name'] = $val; break;
                    case 'Type': $ont_info['type'] = $val; break;
                    case 'State': $ont_info['state'] = $val; break;
                    case 'Configured channel': $ont_info['configured_channel'] = $val; break;
                    case 'Current channel': $ont_info['current_channel'] = $val; break;
                    case 'Admin state': $ont_info['admin_state'] = $val; break;
                    case 'Phase state': $ont_info['phase_state'] = $val; break;
                    case 'Config state': $ont_info['config_state'] = $val; break;
                    case 'Authentication mode': $ont_info['auth_mode'] = $val; break;
                    case 'SN Bind': $ont_info['sn_bind'] = $val; break;
                    case 'Serial number': $ont_info['serial'] = $val; break;
                    case 'Password': $ont_info['password'] = $val; break;
                    case 'Description': $ont_info['description'] = $val; break;
                    case 'Vport mode': $ont_info['vport_mode'] = $val; break;
                    case 'DBA Mode': $ont_info['dba_mode'] = $val; break;
                    case 'ONU Status': $ont_info['onu_status'] = $val; break;
                    case 'OMCI BW Profile': $ont_info['bw_profile'] = $val; break;
                    case 'Line Profile': $ont_info['line_profile'] = $val; break;
                    case 'Service Profile': $ont_info['service_profile'] = $val; break;
                    case 'ONU Distance': $ont_info['onu_distance'] = $val; break;
                    case 'Online Duration': $ont_info['online_duration'] = $val; break;
                    case 'FEC': $ont_info['fec'] = $val; break;
                    case 'FEC actual mode': $ont_info['fec_actual_mode'] = $val; break;
                    case '1PPS+ToD': $ont_info['pps1_tod'] = $val; break;
                    case 'Auto replace': $ont_info['auto_replace'] = $val; break;
                    case 'Multicast encryption': $ont_info['mcast_encrypt'] = $val; break;
                    case 'Multicast encryption current state': $ont_info['mcast_encrypt_current_state'] = $val; break;
                }
            }
        }
        $ont_logs = [];
        foreach (explode("\n", $logs) as $line) {
            if(preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}(.*)/', trim($line), $m)) {
                $ont_logs[] = [
                    '_id' => trim($m[1]),
                    'authpath_time' => trim($m[2]),
                    'dereg_time' => trim($m[3]),
                    'reason' => trim($m[4]),
                ];
            } elseif (preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', trim($line), $m)) {
                $ont_logs[] = [
                    '_id' => trim($m[1]),
                    'authpath_time' => trim($m[2]),
                    'dereg_time' => trim($m[3]),
                    'reason' => '',
                ];
            }
        }
        $ont_info['logs'] = $ont_logs;
        return [
            'type' => 'gpon',
            'data' => $ont_info,
        ];
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}