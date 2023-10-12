<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class ZteConsoleOntInfo extends ModuleAbstract
{
    protected $RESP = [];

    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if (isset($this->RESP[$interface['id']])) {
            $this->response = $this->RESP[$interface['id']];
            return $this;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $type = $interface['_technology'];
        switch ($type) {
            case 'gpon':
                $this->response = $this->getInfoGPON($interface['name']);
                break;
//                case 'epon':
//                    $this->response = $this->getInfoEPON($interface['name']);
//                    break;
            default:
                throw new Exception("Unknown type of interface - '$type'");
        }
        $this->response['interface'] = $interface;
        $this->RESP[$interface['id']] = $this->response;
        return $this;
    }

    private function getInfoGPON($interface)
    {
        //Get gereral information and logs
        $input = $this->telnet->exec("show gpon onu detail-info {$interface}");
        if (!$input) throw new Exception("Empty response on command ' gpon onu detail-info {$interface}'");
        if (preg_match('/No related information to show/', $input)) {
            throw new Exception('No related information to show');
        }
        @list($info, $logs) = explode("------------------------------------------", $input);
        if (!$logs || !$info) {
            throw new Exception("Error parse ont information");
        }
        $lines = explode("\n", $info);
        $ont_info = [];
        foreach ($lines as $line) {
            if (preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $val = trim($m[2]);
                switch (trim($m[1])) {
                    case 'Name':
                        $ont_info['name'] = $val;
                        break;
                    case 'Type':
                        $ont_info['type'] = $val;
                        break;
                    case 'State':
                        $ont_info['state'] = $val;
                        break;
                    case 'Configured channel':
                        $ont_info['configured_channel'] = $val;
                        break;
                    case 'Current channel':
                        $ont_info['current_channel'] = $val;
                        break;
                    case 'Admin state':
                        $ont_info['admin_state'] = $val;
                        break;
                    case 'Phase state':
                        $ont_info['phase_state'] = $val;
                        break;
                    case 'Config state':
                        $ont_info['config_state'] = $val;
                        break;
                    case 'Authentication mode':
                        $ont_info['auth_mode'] = $val;
                        break;
                    case 'SN Bind':
                        $ont_info['sn_bind'] = $val;
                        break;
                    case 'Serial number':
                        $ont_info['serial'] = $val;
                        break;
                    case 'Password':
                        $ont_info['password'] = $val;
                        break;
                    case 'Description':
                        $ont_info['description'] = $val;
                        break;
                    case 'Vport mode':
                        $ont_info['vport_mode'] = $val;
                        break;
                    case 'DBA Mode':
                        $ont_info['dba_mode'] = $val;
                        break;
                    case 'ONU Status':
                        $ont_info['onu_status'] = $val;
                        break;
                    case 'OMCI BW Profile':
                        $ont_info['bw_profile'] = $val;
                        break;
                    case 'Line Profile':
                        $ont_info['line_profile'] = $val;
                        break;
                    case 'Service Profile':
                        $ont_info['service_profile'] = $val;
                        break;
                    case 'ONU Distance':
                        $ont_info['onu_distance'] = $val;
                        break;
                    case 'Online Duration':
                        $ont_info['online_duration'] = $val;
                        break;
                    case 'FEC':
                        $ont_info['fec'] = $val;
                        break;
                    case 'FEC actual mode':
                        $ont_info['fec_actual_mode'] = $val;
                        break;
                    case '1PPS+ToD':
                        $ont_info['pps1_tod'] = $val;
                        break;
                    case 'Auto replace':
                        $ont_info['auto_replace'] = $val;
                        break;
                    case 'Multicast encryption':
                        $ont_info['mcast_encrypt'] = $val;
                        break;
                    case 'Multicast encryption current state':
                        $ont_info['mcast_encrypt_current_state'] = $val;
                        break;
                }
            }
        }
        $ont_logs = [];
        foreach (explode("\n", $logs) as $line) {
            if (preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}(.*)/', trim($line), $m)) {
                if (trim($m[2]) == '0000-00-00 00:00:00') {
                    continue;
                }
                $ont_logs[] = [
                    '_id' => trim($m[1]),
                    'authpath_time' => trim($m[2]),
                    'dereg_time' => trim($m[3]),
                    'reason' => trim($m[4]),
                ];
            } elseif (preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', trim($line), $m)) {
                if (trim($m[2]) == '0000-00-00 00:00:00') {
                    continue;
                }
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