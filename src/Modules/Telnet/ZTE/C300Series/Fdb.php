<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;
use InvalidArgumentException;

class Fdb extends C300ModuleAbstract
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        if($interface['type'] !== 'ONU') {
            $command = "show mac-real-time {$interface['technology']} olt {$interface['name']}";
        } else {
            $command = "show mac-real-time {$interface['technology']} onu {$interface['name']}";
        }

        $lines = explode("\n",$this->telnet->exec($command));
        if(preg_match('/Total mac address : ([0-9]{1,})/', trim($lines[0]), $matches)) {
            if($matches[1] == 0) {
                return  $this;
            }
        }
        $lines = array_splice($lines, 2);
        $this->response = [];
        foreach ($lines as $line) {
            $response = [];
            if(preg_match('/^([[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4})[ ]{1,}([0-9]{1,4})[ ]{1,}(\S*)[ ]{1,}((gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3}))[ ]{1,}(.*)$/', trim($line), $m)) {
                if(preg_match('/vport ([0-9]{1,})[ ]{1,}(.*)/', trim($m[11]), $vport)) {
                    $response['uni'] = $vport[1];
                    $response['time'] = $vport[2];
                } else {
                    $response['uni'] = null;
                    $response['time'] = $m[11];
                }
                $response['mac'] = $this->macTo6octets($m[1]);
                $response['vlan_id'] = (int) $m[2];
                $response['type'] = $m[3];
                $response['interface'] = $this->parseInterface($m[4]);
                $this->response[] = $response;
            }
        }
        return  $this;
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