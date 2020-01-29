<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class AddressListControl extends ExecCommand
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
    private function getIdsByParams($name = "", $address = "") {

        $ids = [];
        foreach ($this->module->address_list_info->run([
            'name' => $name,
            'address' => $address,
        ])->getPrettyFiltered() as $id) {
            $ids[] = $id['_id'];
        }
        return $ids;
    }
    public function run($params = [])
    {
        if($params['action'] == 'add') {
            if(!$params['ip']) throw new \InvalidArgumentException("IP address is required for adding");
            if(!$params['mac']) throw new \InvalidArgumentException("MAC address is required for adding");
            if(!$params['vlan_id'] && !$params['vlan_name']) throw new \InvalidArgumentException("VLAN name or id is required for adding");
            return $this->add($params);
        }
        if($params['action'] == 'remove') {
            if(!$params['ip'] && !$params['mac']) throw new \InvalidArgumentException("Not all arguments passed for removing");
            return $this->remove($params);
        }
        throw new \Exception("StaticArpControl support only add|remove methods");
    }
    private function add($params) {
        $interface = $params['vlan_name'];
        if($params['vlan_id']) {
            $interface = $this->getInterfaceNameById($params['vlan_id']);
        }

        $result = $this->execComm("/ip/arp/add", [
            'address'=>$params['ip'],
            'mac-address'=>$params['mac'],
            'interface'=>$interface,
            'comment'=>$params['comment'],
        ]);
        $this->response = [$result];
        return $this;
    }
    private function getArpsInfoByParam($params) {
        return $this->obj->arp_info->run($params)->getPrettyFiltered();
    }

    private function remove($params) {
        $arps = $this->getArpsInfoByParam($params);
        $ids = [];
        if(!$arps) {
            throw new \Exception("Arp not found by parameters");
        }
        foreach ($arps as $arp) {
            $this->execComm("/ip/arp/remove", [
                'numbers'=>$arp['extra']['id']
            ]);
            $ids[] = $arp['extra']['id'];
        }
        return $this;
    }
}