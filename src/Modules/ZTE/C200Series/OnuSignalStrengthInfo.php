<?php


namespace SwitcherCore\Modules\ZTE\C200Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class OnuSignalStrengthInfo extends ModuleAbstract
{
    public function run($params = [])
    {
        $this->response = [];
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $interface = $this->parseInterface($params['interface']);
        if($interface['type'] == 'ONU') {
            $ret = $this->getSignalsByInterface($interface['name']);
            $ret['interface'] = $interface;
            $this->response[] = $ret;
            return $this;
        }
        $interfaces = $this->getModule('interfaces_list')->run(['interface' => null, 'parent' => $interface['id']])->getPrettyFiltered(['interface' => null, 'parent' => $interface['id']]);
        foreach ($interfaces as $iface) {
            if($iface['meta']['online_status'] !== 'Online') {
                $ret = [
                    'olt_rx' => null,
                    'onu_rx' => null,
                ];
            } else {
                $ret = $this->getSignalsByInterface($iface['name']);
            }
            $ret['interface'] = $iface;
            $this->response[] = $ret;
        }
        return $this;
    }
    public function getSignalsByInterface($interfaceName) {
        $onuRx = $this->telnet->exec("show pon power onu-rx {$interfaceName}");
        $oltRx = $this->telnet->exec("show pon power olt-rx {$interfaceName}");
        $ret = [
            'olt_rx' => null,
            'onu_rx' => null,
        ];
        if(preg_match('/.*: (.*?)\(dbm\)/', trim($onuRx), $m)) {
            $ret['onu_rx'] = (float)$m[1];
        }
        if(preg_match('/.*: (.*?)\(dbm\)/', trim($oltRx), $m)) {
            $ret['olt_rx'] =  (float)$m[1];
        }
        return $ret;
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