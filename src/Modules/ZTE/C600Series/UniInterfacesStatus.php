<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class UniInterfacesStatus extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        if($this->model->getKey() === 'zte_c610_fw_12') {
            $this->response = $this->getGponInfoC610($interface);
        } else {
            $this->response = $this->getGponInfo($interface);
        }

        return $this;
    }

    public function getGponInfo($interface) {
        $resp = $this->parseExpandedTable($this->telnet->exec("show gpon remote-onu interface eth {$interface['name']}"));
        $response = [];
        foreach ($resp as $num => $info) {
            foreach ($info as $k => $v) {
                switch ($k) {
                    case 'speed_status':
                        $response[$num]['speed'] = $v === 'auto' ? 'Down' : ucfirst($v);
                        break;
                    case 'operate_status':
                        if($v === 'enable') $v = 'Up';
                        if($v === 'disable') $v = 'Down';
                        $response[$num]['status'] =   ucfirst($v);
                        break;
                    case 'admin_status':
                        $response[$num]['admin_state'] = $v === 'unlock' ? 'Enabled' : ucfirst($v);
                        break;
                    case 'interface':
                        $response[$num]['num'] = $v;
                        break;
                    case 'speed_config':
                        $response[$num]['admin_speed'] = ucfirst($v);
                        break;
                    case 'ether_loop':
                    case 'power_control':
                        $response[$num][$k] = ucfirst($v);
                        break;
                }
            }
        }
        return [['interface' => $interface, 'unis' => $response]];
    }

    public function getGponInfoC610($interface) {
        $response = [];
        for($portNum = 1; $portNum <= 24; $portNum++) {
            try {
                $resp = $this->parseExpandedTable($this->telnet->exec("show gpon remote-onu interface eth {$interface['name']} eth_0/{$portNum}"));
                foreach ($resp as $num => $info) {
                    foreach ($info as $k => $v) {
                        switch ($k) {
                            case 'speed_status':
                                $response[$num]['speed'] = $v === 'auto' ? 'Down' : ucfirst($v);
                                break;
                            case 'operate_status':
                                if ($v === 'enable') $v = 'Up';
                                if ($v === 'disable') $v = 'Down';
                                $response[$num]['status'] = ucfirst($v);
                                break;
                            case 'admin_status':
                                $response[$num]['admin_state'] = $v === 'unlock' ? 'Enabled' : ucfirst($v);
                                break;
                            case 'interface':
                                $response[$num]['num'] = $v;
                                break;
                            case 'speed_config':
                                $response[$num]['admin_speed'] = ucfirst($v);
                                break;
                            case 'ether_loop':
                            case 'power_control':
                                $response[$num][$k] = ucfirst($v);
                                break;
                        }
                    }
                }
            } catch (\Exception $e) {
                break;
            }
        }
        return [['interface' => $interface, 'unis' => $response]];
    }


    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}