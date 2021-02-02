<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class OntStateInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parsePortByName($params['interface']);
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getStateGPON($params['interface']); break;
            case 'epon': $this->response = $this->getStateEPON($params['interface']); break;
            default: throw new Exception("Unknown type of interface - '$type'");
        }
        return $this;
    }

    private function getStateEPON($interface)
    {
        $input = $this->telnet->exec("show epon onu state {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        $lines = explode("\n", $input);
        $response = [];
        foreach (array_splice($lines, 2) as $line) {
            if(preg_match('/^(.*)[ ]{1,}(Power Off|Online)[ ]{1,}(idle|complete)[ ]{1,}(.*)$/', trim($line), $matches)) {
                $response[] = [
                    'onu' => trim($matches[1]),
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
        $input = $this->telnet->exec("show gpon onu state {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        if(preg_match('/No related information to show/', $input)) {
            throw new Exception('No related information to show');
        }
        $rows = explode("\n", $input);
        unset($rows[count($rows) -1]);
        $response = $this->parseTable($rows);
        foreach ($response as $k=>$resp) {
            $response[$k]['onu'] = 'gpon-onu_' . $resp['onu_index'];
            unset($response[$k]['onu_index']);
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