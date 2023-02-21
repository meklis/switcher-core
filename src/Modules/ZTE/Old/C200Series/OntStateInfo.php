<?php


namespace SwitcherCore\Modules\ZTE\Old\C200Series;



use Exception;
use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class OntStateInfo extends ModuleAbstract
{
    public $cached = [];
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parseInterface($params['interface']);
        if (isset($this->cached[$interface['id']])) {
            $this->response = $this->cached[$interface['id']];
            return $this;
        }
        $type = $interface['technology'];
        switch ($type) {
            case 'gpon': $this->response = $this->getStateGPON($params['interface']); break;
            case 'epon': $this->response = $this->getStateEPON($params['interface']); break;
            default: throw new Exception("Unknown type of interface - '$type'");
        }
        $this->cached[$interface['id']] = $this->response;
        return $this;
    }

    private function getStateEPON($interface)
    {
        $input = $this->telnet->exec("show onu all-status {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        $lines = explode("\n", $input);
        $response = [];
        foreach (array_splice($lines, 2) as $line) {
            if(preg_match('/^(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)$/', trim($line), $matches)) {
                $interface = $this->parseInterface(trim($matches[1]));
                $response[] = [
                    'interface' => $interface,
                    'online_status' => trim($matches[3]),
                    'oam_status' => null,
                    'reg_status' =>  trim($matches[2]),
                    'mac' => null,
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
         throw new \Exception("GPON not supported");
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