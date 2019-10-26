<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class ArpInfo extends ExecCommand
{

    protected $status = false;
    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
    public function run($params = [])
    {
        $arps = [];
        $vlans = [];
        foreach ($this->getDependencyModule('interface_vlan_info')->run()->getPrettyFiltered() as $vl) {
            $vlans[$vl['name']] = $vl;
        }
        $filter = [];
        $filter['?complete'] = 'true';
        if(isset($params['ip']) && $params['ip']) {
          $filter['?address'] = $params['ip'];
        }
        if(isset($params['mac']) && $params['mac']) {
          $filter['?mac-address'] = $params['mac'];
        }
        if(isset($params['vlan_id']) && $params['vlan_id']) {
            $interface = "";
            foreach ($vlans as $vlan) {
                if($vlan['vlan_id'] == $params['vlan_id']) {
                    $interface = $vlan['name'];
                    break;
                }
            }
          $filter['?interface'] = $interface;
        }
        foreach ($this->execComm('/ip/arp/print', $filter) as $a) {
            if(!isset($vlans[$a['interface']]['vlan_id'])) continue;
            $arps[] = [
                'ip' => $a['address'],
                'mac' => $a['mac-address'],
                'dynamic' => $a['dynamic'],
                'comment' => isset($a['comment']) ? $a['comment'] : "",
                'vlan_id' => $vlans[$a['interface']]['vlan_id'],
            ];
        }
        $this->response = $arps;
        return $this;
    }
}