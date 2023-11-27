<?php

namespace SwitcherCore\Modules\ZTE\C300Series;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class UniInterfacesStatus extends \SwitcherCore\Modules\ZTE\ModuleAbstract
{
    public function run($params = [])
    {
        if (!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        switch ($interface['_technology']) {
            case 'epon': $this->response = $this->getEponInfo($interface); break;
            case 'gpon': $this->response = $this->getGponInfo($interface); break;
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
                        $response[$num]['admin_state'] = $v === 'unlock' ? 'Enabled' : 'Disabled';
                        break;
                    case 'interface':
                        if(preg_match('/^eth.*?\/([0-9]{1,2})/', $v, $m)) {
                            $response[$num]['num'] = $m[1];
                            $response[$num]['name'] = $v;
                        } else {
                            $response[$num]['num'] = $v;
                        }
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
            if(isset($response[$num]['speed']) && $response[$num]['speed'] === 'Down') {
                $response[$num]['status'] = 'Down';
            }
        }
        return [['interface' => $interface, 'unis' => $response]];
    }

    public function getEponInfo($interface) {
        $oids = array_map(function ($oid) use ($interface) {
            return Oid::init("{$oid->getOid()}.{$interface['_oid_id']}");
        }, $this->oids->getOidsByRegex("^epon.uni.*"));
        $response = $this->formatResponse($this->snmp->walkNext($oids));
        $data = [];
        foreach ($response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            foreach ($dt->fetchAll() as $resp) {
                $uni = Helper::getIndexByOid($resp->getOid());
                $name = str_replace(['epon.uni.'], '', $oidName);
                switch ($oidName) {
                    case 'epon.uni.status':
                    case 'epon.uni.admin_state':
                    case 'epon.uni.duplex':
                    case 'epon.uni.speed':
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1));
                        $data[$iface['id']]['interface'] = $iface;
                        $data[$iface['id']]['unis'][$uni]['num'] = $uni;
                        $data[$iface['id']]['unis'][$uni][$name] = $resp->getParsedValue();
                        break;
                }
            }
        }
        return array_values(array_map(function ($onu) {
            foreach ($onu['unis'] as $num=>$uni) {
                if($uni['speed'] === 'Down') {
                    $uni['status'] = 'Down';
                } else {
                    $uni['status'] = 'Up';
                }
                $onu['unis'][$num] = $uni;
            }
            $onu['unis'] = array_values($onu['unis']);
            return $onu;
        }, $data));
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