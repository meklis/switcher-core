<?php


namespace SwitcherCore\Modules\ZTE\C200Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class OntInfo extends ModuleAbstract
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getInfoGPON($interface['name']); break;
            case 'epon': $this->response = $this->getInfoEPON($interface['name']); break;
            default: throw new Exception("Unknown type of interface - '$type'");
        }
        $this->response['interface'] = $interface;
        return $this;
    }

    private function getInfoEPON($interface)
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

    private function getInfoGPON($interface)
    {
        throw new \Exception("GPON not realized");
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