<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class Fdb extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = [];
        if($params['interface'] && $params['onu']) {
            throw new \InvalidArgumentException("Only one of parameter allowed");
        } elseif($params['interface']) {
            $technology = $this->parsePortByName($params['interface'])['technology'];
            $command = "show mac-real-time $technology olt {$params['interface']}";
        } elseif ($params['onu']) {
            $technology = $this->parsePortByName($params['onu'])['technology'];
            $command = "show mac-real-time $technology onu {$params['interface']}";
        } else {
            throw new \InvalidArgumentException("One of param 'interface' or 'onu' is required");
        }

        $lines = explode("\n",$this->obj->telnet->exec($command));
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
                    $response['vport'] = $vport[1];
                    $response['time'] = $vport[2];
                } else {
                    $response['vport'] = null;
                    $response['time'] = $m[11];
                }
                $response['mac'] = $this->macTo6octets($m[1]);
                $response['vlan_id'] = (int) $m[2];
                $response['type'] = $m[3];
                $response['onu'] = $m[4];
                $response['_interface'] = $this->parsePortByName($m[4]);
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