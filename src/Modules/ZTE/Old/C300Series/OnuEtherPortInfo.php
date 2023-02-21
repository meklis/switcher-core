<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;



use InvalidArgumentException;
use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class OnuEtherPortInfo extends ModuleAbstract
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if($interface['type'] !== 'ONU') {
            throw new \Exception("Only ONU interface is allowed");
        }
       switch ($interface['technology']) {
            case 'gpon': $this->response = $this->getDataGPON($interface['name']); break;
            case 'epon': throw new InvalidArgumentException("Not realized for epon onu");
            default: throw new InvalidArgumentException("Incorrect onu name");
        }
        return $this;
    }
    private function getDataGpon($onu) {
        $resp = $this->parseExpandedTable($this->telnet->exec("show gpon remote-onu interface eth {$onu}"));
        foreach ($resp as $num=>$info) {
            foreach ($info as $k=>$v) {
                switch ($k) {
                    case 'speed_status': $resp[$num][$k] = $v === 'auto' ? 'Down' : ucfirst($v); break;
                    case 'operate_status': $resp[$num][$k] = $v === 'disable' ? 'Down' : 'Up'; break;
                    case 'admin_status': $resp[$num][$k] = $v === 'unlock' ? 'Enabled' : ucfirst($v); break;
                    case 'arc':
                    case 'arc_interval':
                    case 'expect_type':
                    case 'pause_time':
                    case 'status_changes': $resp[$num][$k] = (int) $v; break;
                    case 'speed_config':
                    case 'pp_po_filter':
                    case 'ether_loop':
                    case 'power_control': $resp[$num][$k] = ucfirst($v); break;
                }
            }
        }
        return $resp;
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