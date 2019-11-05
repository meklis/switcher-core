<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class DhcpServerInfo extends ExecCommand
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
    private function getInterface($params) {
        foreach ($this->getDependencyModule('interface_vlan_info')->run($params)->getPrettyFiltered() as $vl) {
            return $vl;
        }
        return null;
    }
    public function run($params = [])
    {
        $filter = [];
        if((isset($params['vlan_id']) && $params['vlan_id']) || (isset($params['vlan_name']) && $params['vlan_name'])) {
            $filter['?interface'] = $this->getInterface(['vlan_id' => $params['vlan_id'], 'name' => $params['vlan_name']])['name'];
        }
        if(isset($params['name']) && $params['name']) {
            $filter['?name'] = $params['name'];
        }
        $response = [];
        foreach ($this->execComm('/ip/dhcp-server/print', $filter) as $a) {
            $response[] = [
                'name' => $a['name'],
                'interface' => $a['interface'],
                'lease_time' => $a['lease-time'],
                'address_pool' => $a['address-pool'],
                'extra' => [
                    'vlan' => $this->getInterface(['name' => $a['interface']]),
                ]
            ];
        }
        $this->response = $response;
        return $this;
    }
}