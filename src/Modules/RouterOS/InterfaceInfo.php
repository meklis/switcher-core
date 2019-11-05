<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class InterfaceInfo extends ExecCommand
{

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
        $vlans = [];
        $formatedResp = [];
        if(isset($params['name']) && $params['name']) {
            $formatedResp['?name'] = $params['name'];
        }
        if(isset($params['vlan_id']) && $params['vlan_id']) {
            $formatedResp['?vlan-id'] = $params['vlan_id'];
        }
        foreach ($this->execComm('/interface/vlan/print', $formatedResp) as $vl) {
            $vlans[] = [
                'vlan_id' => $vl['vlan-id'],
                'name' => $vl['name'],
                'disabled' => $vl['disabled'],
                'arp' => $vl['arp'],
            ];
        }
        $this->response = $vlans;
        return $this;
    }
}