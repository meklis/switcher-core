<?php


namespace SwitcherCore\Modules\ZTE\C200Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class OnuSignalStrengthInfo extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $interface = $this->parseInterface($params['interface']);

        $onuRx = $this->telnet->exec("show pon power onu-rx {$interface['name']}");
        $oltRx = $this->telnet->exec("show pon power olt-rx {$interface['name']}");

        $response = [];
        foreach ($oltRx as $onu=>$v) {
            $iface = $this->parseInterface($onu);
            $response[$iface['id']]['interface'] = $iface;
            if(!is_numeric($v)) {
                $v = null;
            }
            $response[$iface['id']]['olt_rx'] = $v;
            if(isset($onuRx[$onu]) && is_numeric($onuRx[$onu])) {
                $response[$iface['id']]['onu_rx'] = $onuRx[$onu];
            } else {
                $response[$iface['id']]['onu_rx'] = null;
            }
        }
        $this->response = array_values($response);
        return $this;
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