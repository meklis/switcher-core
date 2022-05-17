<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;

class OnuSignalStrengthInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $interface = $this->parseInterface($params['interface']);
        $onuRx = [];
        $oltRx = [];
        $raw = explode("\n",$this->telnet->exec("show pon power onu-rx {$interface['name']}"));
        foreach ($raw as $line) {
            if(preg_match('/^(gpon-onu|epon-onu)(.*?)[ ]{1,}(.*)$/', $line, $matches)) {
                 $onuRx["{$matches[1]}{$matches[2]}"] = str_replace('(dbm)', '', $matches[3]);
            }
        }
        $raw = explode("\n",$this->telnet->exec("show pon power olt-rx {$interface['name']}"));
        foreach ($raw as $line) {
            if(preg_match('/^(gpon-onu|epon-onu)(.*?)[ ]{1,}(.*)$/', $line, $matches)) {
                $oltRx["{$matches[1]}{$matches[2]}"] = str_replace('(dbm)', '', $matches[3]);
            }
        }
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