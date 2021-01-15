<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class OntStateInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parsePortByName($params['interface']);
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getStateGPON($params['interface']); break;
            case 'epon': $this->response = $this->getStateEPON($params['interface']); break;
            default: throw new \Exception("Unknown type of interface - '$type'");
        }
        return $this;
    }

    private function getStateEPON($interface)
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

    private function getStateGPON($interface)
    {
        $input = $this->obj->telnet->exec("show gpon onu state {$interface}");
        if (!$input) throw new \Exception("Empty response on command 'show epon onu state {$interface}'");
        if(preg_match('/No related information to show/', $input)) {
            throw new \Exception('No related information to show');
        }
        $lines = explode("\n", $input);
        $response = [];
        foreach (array_splice($lines, 2) as $line) {
            if(preg_match('/^([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{1,2}:[0-9]{1,3})[ ]{2,}(.*?)[ ]{2,}(.*?)[ ]{2,}(.*?)[ ]{2,}(.*)$/', trim($line), $matches)) {
                $response[] = [
                    'interface' => 'gpon-onu_' . trim($matches[1]),
                    'admin_state' => trim($matches[2]),
                    'omcc_state' => trim($matches[3]),
                    'phase_state' => trim($matches[4]),
                    'channel' => trim($matches[5]),
                ];
            }
        }
        return [
            'data' => $response,
            'type' => 'gpon',
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