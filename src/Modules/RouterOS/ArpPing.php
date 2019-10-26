<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class ArpPing extends ExecCommand
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

        if(!(isset($params['vlan_id']) || isset($params['vlan_name']))) {
            throw new \InvalidArgumentException("vlan_id or vlan_name(interface name) required for this module");
        }
        $vlans = [];
        foreach ($this->getDependencyModule('interface_vlan_info')->run()->getPrettyFiltered() as $vl) {
            $vlans[$vl['name']] = $vl;
        }
        $filter = [];
        $filter['arp-ping'] = 'true';
        if(isset($params['ip']) && $params['ip']) {
          $filter['address'] = $params['ip'];
        }
        if(isset($params['vlan_id']) && $params['vlan_id']) {
            $interface = "";
            foreach ($vlans as $vlan) {
                if($vlan['vlan_id'] == $params['vlan_id']) {
                    $interface = $vlan['name'];
                    break;
                }
            }
          $filter['interface'] = $interface;
        }
        if(isset($params['vlan_name']) && $params['vlan_name']) {
            $filter['interface'] = $params['vlan_name'];
        }
        if(isset($params['count']) && $params['count']) {
            $filter['count'] = $params['count'];
        } else {
            $filter['count'] = 1;
        }
        $this->response = $this->execComm('/ping', $filter);
        return $this;
    }
}