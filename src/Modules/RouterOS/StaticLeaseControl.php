<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class StaticLeaseControl extends ExecCommand
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
    private function getDhcpServer($params) {
        foreach ($this->getDependencyModule('dhcp_server_info')->run($params)->getPrettyFiltered() as $vl) {
            return $vl;
        }
        throw new \Exception("DHCP-server not found");
    }
    public function run($params = [])
    {
        if($params['action'] == 'add') {
            if(!$params['ip']) throw new \InvalidArgumentException("IP address is required for adding");
            if(!$params['mac']) throw new \InvalidArgumentException("MAC address is required for adding");
            if(!$params['vlan_id'] && !$params['vlan_name'] && !$params['server']) throw new \InvalidArgumentException("VLAN name or id is required for adding");
            return $this->add($params);
        }
        if($params['action'] == 'remove') {
            if(!$params['ip'] && !$params['mac']) throw new \InvalidArgumentException("Not all arguments passed for removing");
            return $this->remove($params);
        }
        throw new \Exception("StaticArpControl support only add|remove methods");
    }
    private function add($params) {
        $server = $params['dhcp_server'];
        if($params['vlan_id'] || $params['vlan_name']) {
            $server = $this->getDhcpServer($params)['name'];
        }

        $result = $this->execComm("/ip/dhcp-server/lease/add", [
            'address'=>$params['ip'],
            'mac-address'=>$params['mac'],
            'server'=>$server,
            'comment'=>$params['comment'],
        ]);
        $this->response = [$result];
        return $this;
    }
    private function getLeasesByParam($params) {
        return $this->getDependencyModule('lease_info')->run($params)->getPrettyFiltered();
    }

    private function remove($params) {
        $arr = $this->getLeasesByParam($params);
        $ids = [];
        if(!$arr) {
            throw new \Exception("Arp not found by parameters");
        }
        foreach ($arr as $a) {
            $this->execComm("/ip/dhcp-server/lease/remove", [
                'numbers'=>$a['extra']['id']
            ]);
            $ids[] = $a['extra']['id'];
        }
        return $this;
    }
}