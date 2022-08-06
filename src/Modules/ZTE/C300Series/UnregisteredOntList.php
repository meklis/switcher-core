<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class UnregisteredOntList extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $result = [];
        switch ($params['type']) {
            case '':
            case 'all':
                $result = array_merge($result, $this->getUnregisteredGPON());
                $result = array_merge($result, $this->getUnregisteredEPON());
                break;
            case 'gpon':
                $result = array_merge($result, $this->getUnregisteredGPON());
                break;
            case 'epon':
                $result = array_merge($result, $this->getUnregisteredEPON());
        }
        foreach ($result as $k=>$v) {
            $result[$k]['interface'] = $this->parseInterface($v['interface']);
        }
        $this->response  =$result;
        return $this;
    }

    private function getUnregisteredEPON()
    {
        $input = $this->telnet->exec("show onu unauthentication");
        if (!$input) throw new Exception("Empty response on command 'show gpon onu uncfg'");
        $lines = explode("\n", $input);
        $response = [];
        $block = [];
        foreach ($lines as $line) {
            if(!trim($line)) {
                if(count($block) > 3) {
                    $response[] = $block;
                }
                $block = [];
                continue;
            }
            if(preg_match('/^(.*?):(.*)$/', $line, $matches)) {
                $key = strtolower(trim($matches[1]));
                $value = trim($matches[2]);
                switch ($key) {
                    case 'onu interface': $block['interface'] = $value; break;
                    case 'onu model': $block['onu_model'] = $value; break;
                    case 'extended model': $block['onu_extended_model'] = $value; break;
                    case 'mac address': $block['mac'] = $value; break;
                    case 'sn': $block['serial'] = $value; break;
                    case 'online state': $block['online_state'] = $value; break;
                    case 'regtime': $block['reg_time'] = $value; break;
                }
                $block['type'] = 'epon';
            }
        }
        if(count($block) > 3) {
            $response[] = $block;
        }
        return $response;
    }

    private function getUnregisteredGPON()
    {
        $list = $this->telnet->exec("show gpon onu uncfg");
        if (!$list) throw new Exception("Empty response on command 'show gpon onu uncfg'");
        //Parsing block
        $lines = explode("\n", $list);
        $lines = array_splice($lines, 2);
        if (count($lines) === 0) return [];
        $result = [];
        foreach ($lines as $line) {
            if (preg_match('/^(\S*)\s*(\S*)\s*(\S*)$/', trim($line), $matches)) {
                $result[] = [
                    'interface' => $matches[1],
                    'serial' => $matches[2],
                    'status' => $matches[3],
                    'type' => 'gpon',
                    'mac' => null,
                ];
            }
        }
        return  $result;
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