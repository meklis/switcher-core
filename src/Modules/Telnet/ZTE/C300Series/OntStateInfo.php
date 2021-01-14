<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class OntStateInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $interface = $this->parsePortByName($params['interface']);
        $type = $interface['type'];
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
            if(preg_match('/^(.*)[ ]{1,}(Power Off|Online)[ ]{1,}(idle|complete)[ ]{1,}(.*)$/', $line, $matches)) {
                $response[] = [
                    'interface' => trim($matches[1]),
                    'online_status' => trim($matches[2]),
                    'oam_status' => trim($matches[3]),
                    'mac' => trim($matches[4]),
                    'type' => 'epon'
                ];
            }
        }
        return $response;
    }

    private function getStateGPON($interface)
    {
        //@TODO realize  get gpon state
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