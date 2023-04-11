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
        $oids = array_map(function ($oid) use ($interface) {
            if($interface['_technology'] == 'epon') {
                return Oid::init("{$oid->getOid()}.{$interface['_oid_id']}");
            } else {
                return Oid::init("{$oid->getOid()}.{$interface['_oid_eth_id']}");
            }
        }, $this->oids->getOidsByRegex("^{$interface['_technology']}.uni.*"));
        $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        return $this;
    }

    public function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            foreach ($dt->fetchAll() as $resp) {
                $uni = Helper::getIndexByOid($resp->getOid());
                $name = str_replace(['gpon.uni.', 'epon.uni.'], '', $oidName);
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
                    case 'gpon.uni.admin_state':
                    case 'gpon.uni.speed':
                    case 'gpon.uni.status':
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 2) . '.' . Helper::getIndexByOid($resp->getOid(), 1), 'eth_id');
                        $data[$iface['id']]['interface'] = $iface;
                        $data[$iface['id']]['unis'][$uni]['num'] = $uni;
                        $data[$iface['id']]['unis'][$uni][$name] = $resp->getParsedValue();
                        break;
                }
            }
        }
        return array_values(array_map(function ($onu) {
            foreach ($onu['unis'] as $num=>$uni) {
                $onu['unis'][$num] = $uni;
            }
            $onu['unis'] = array_values($onu['unis']);
            return $onu;
        }, $data));
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}