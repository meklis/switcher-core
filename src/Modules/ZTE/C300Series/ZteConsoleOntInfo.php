<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class ZteConsoleOntInfo extends ModuleAbstract
{
    protected $RESP = [];
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if(isset($this->RESP[$interface['id']])) {
            $this->response = $this->RESP[$interface['id']];
            return $this;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $type = $interface['_technology'];
        if(strpos($this->model->getName(), "1.2") !== false) {
            switch ($type) {
                case 'gpon':
                    $this->response = $this->getInfoFw12GPON($interface['name']);
                    break;
                case 'epon':
                    $this->response = $this->getInfoFw12EPON($interface['name']);
                    break;
                default:
                    throw new Exception("Unknown type of interface - '$type'");
            }
        } else {
            switch ($type) {
                case 'gpon':
                    $this->response = $this->getInfoGPON($interface['name']);
                    break;
                case 'epon':
                    $this->response = $this->getInfoEPON($interface['name']);
                    break;
                default:
                    throw new Exception("Unknown type of interface - '$type'");
            }
        }
        $this->response['interface'] = $interface;
        $this->RESP[$interface['id']] = $this->response;
        return $this;
    }

    private function getInfoEPON($interface)
    {
        $input = $this->telnet->exec("show pon onu information {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        @list($info, $logs) = @explode("------------------------------------------", $input);
        if(!$logs || !$info) {
            throw new Exception("Error parse ont information");
        }
        $lines = explode("\n", $info);
        $ont_info = [];
        foreach ($lines as $line) {
            if(preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $val = trim($m[2]);
                switch (trim($m[1])) {
                    case 'Auth information': $ont_info['auth_info'] = $val; break;
                    case 'ONU interface': $ont_info['interface'] = $val; break;
                    case 'State': $ont_info['state'] = $val; break;
                    case 'MAC reported': $ont_info['mac'] = $val; break;
                    case 'LLID': $ont_info['llid'] = $val; break;
                    case 'ONU type configured': $ont_info['type_configured'] = $val; break;
                    case 'ONU type reported': $ont_info['type_reported'] = $val; break;
                    case 'Hardware version': $ont_info['ver_hardware'] = $val; break;
                    case 'Firmware version': $ont_info['ver_firmware'] = $val; break;
                    case 'Software version': $ont_info['ver_software'] = $val; break;
                }
            }
        }
        $ont_logs = [];
        foreach (explode("\n", $logs) as $line) {
            if(preg_match('/([0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})(.*)$/', trim($line), $m)) {
                if(strpos( $m[1],"0000") !== false) continue;
                $ont_logs[] = [
                    'reg_time' => str_replace("/", "-", trim($m[1])),
                    'authpath_time' => str_replace("/", "-", trim($m[2])),
                    'dereg_time' => str_replace("/", "-", trim($m[3])),
                    'reason' => trim($m[4]),
                ];
            }
        }
        $ont_info['logs'] = $ont_logs;
        return [
            'data' => $ont_info,
            'type' => 'epon',
        ];
    }

    private function getInfoGPON($interface)
    {
        //Get gereral information and logs
        $input = $this->telnet->exec("show gpon onu detail-info {$interface}");
        if (!$input) throw new Exception("Empty response on command ' gpon onu detail-info {$interface}'");
        if(preg_match('/No related information to show/', $input)) {
            throw new Exception('No related information to show');
        }
        @list($info, $logs) = explode("------------------------------------------", $input);
        if(!$logs || !$info) {
            throw new Exception("Error parse ont information");
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
                if(trim($m[2]) == '0000-00-00 00:00:00') {
                    continue;
                }
                $ont_logs[] = [
                    '_id' => trim($m[1]),
                    'authpath_time' => trim($m[2]),
                    'dereg_time' => trim($m[3]),
                    'reason' => trim($m[4]),
                ];
            } elseif (preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', trim($line), $m)) {
                if(trim($m[2]) == '0000-00-00 00:00:00') {
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

    private function getInfoFw12EPON($interface)
    {
        $input = $this->telnet->exec("show onu detail {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        $lines = explode("\n", $input);
        $ont_info = [];
        foreach ($lines as $line) {
            if(preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $val = trim($m[2]);
                switch (trim($m[1])) {
                    case 'Onu interface': $ont_info['interface'] = $val; break;
                    case 'AdminState': $ont_info['admin_state'] = $val; break;
                    case 'Physical State': $ont_info['state'] = $val; break;
                    case 'Online State': $ont_info['online_state'] = $val; break;
                    case 'OnuType': $ont_info['type'] = $val; break;
                    case 'MAC': $ont_info['mac'] = $val; break;
                    case 'NAME': $ont_info['name'] = $val; break;
                    case 'Register time': $ont_info['reg_time'] = $val; break;
                    case 'Authpass time': $ont_info['authpass_time'] = $val; break;
                    case 'Deregister time': $ont_info['deregister_time'] = $val; break;
                    case 'FecMode': $ont_info['fec_mode'] = $val; break;
                    case 'MAC bind mode': $ont_info['mac_bind_mode'] = $val; break;
                }
            }
        }
        $ont_info['logs'] = [];
        return [
            'data' => $ont_info,
            'type' => 'epon',
        ];
    }

    private function getInfoFw12GPON($interface)
    {
        //Get gereral information and logs
        $input = $this->telnet->exec("show gpon onu detail-info {$interface}");
        if (!$input) throw new Exception("Empty response on command ' gpon onu detail-info {$interface}'");
        if(preg_match('/No related information to show/', $input)) {
            throw new Exception('No related information to show');
        }
        @list($info, $logs) = explode("------------------------------------------", $input);
        if(!$logs || !$info) {
            throw new Exception("Error parse ont information");
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
                if(trim($m[2]) == '0000-00-00 00:00:00') {
                    continue;
                }
                $ont_logs[] = [
                    '_id' => trim($m[1]),
                    'authpath_time' => trim($m[2]),
                    'dereg_time' => trim($m[3]),
                    'reason' => trim($m[4]),
                ];
            } elseif (preg_match('/^([0-9]{1,3})[ ]{1,3}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})[ ]{1,}([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', trim($line), $m)) {
                if(trim($m[2]) == '0000-00-00 00:00:00') {
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

}