<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;

class OntStateInfoFW12 extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parseInterface($params['interface']);
        if($cached = $this->getCache("iface-{$interface['id']}")) {
            $this->response = $cached;
            return $this;
        }
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getStateGPON($params['interface']); break;
            case 'epon': $this->response = $this->getStateEPON($params['interface']); break;
            default: throw new Exception("Unknown type of interface - '$type'");
        }
        $this->setCache("iface-{$interface['id']}", $this->response, 10);
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
                $interface = $this->parseInterface(trim($matches[1]));
                $response[] = [
                    'interface' => $interface,
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
        $response = $this->parseGponRows($rows);
        foreach ($response as $k=>$resp) {
            $interface = $this->parseInterface(  $resp['onu_index']);
            $response[$k]['interface'] = $interface;
            unset($response[$k]['onu_index']);
        }
        return [
            'data' => $response,
            'type' => 'gpon',
            ];
    }
    protected function parseGponRows($rows) {
        unset($rows[1]);
        unset($rows[0]);
        $response = [];
        foreach ($rows as $row) {
            if(preg_match('/^(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*)$/', $row, $match)) {
                $response[] = [
                  'onu_index' => $match[1],
                  'admin_state' => $match[2],
                  'state' => $match[3],
                  'phase_state' => $match[4],
                  'channel' => $match[5],
                ];
            }
        }
        return $response;
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