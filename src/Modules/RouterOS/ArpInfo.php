<?php


namespace SwitcherCore\Modules\RouterOS;


class ArpInfo extends ExecCommand
{

    protected $status = false;
    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        if(isset($filter['status']) && $filter['status']) {
            return array_filter($this->response, function ($e) use ($filter) {
               if($filter['status'] == $e['status']) return true;
               return false;
            });
        }
        return $this->response;
    }
    public function run($params = [])
    {
        $arps = [];
        $vlans = [];
        foreach ($this->getModule('interface_vlan_info')->run()->getPrettyFiltered() as $vl) {
            $vlans[$vl['name']] = $vl;
        }
        $filter = [];
        if(isset($params['ip']) && $params['ip']) {
          $filter['?address'] = $params['ip'];
        }
        if(isset($params['mac']) && $params['mac']) {
          $filter['?mac-address'] = $params['mac'];
        }
        if(isset($params['vlan_name']) && $params['vlan_name']) {
            $filter['?interface'] = $params['vlan_name'];
        }
        if(isset($params['interface']) && $params['interface']) {
            $filter['?interface'] = $params['interface'];
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
            $status = 'OK';
            if($a['invalid'] == 'true') $status='invalid';
            if($a['disabled'] == 'true') $status='disabled';
            $arps[] = [
                '_id' => $a['.id'],
                'interface' => $a['interface'],
                'ip' => $a['address'],
                'mac' => isset($a['mac-address']) ? $a['mac-address'] : null,
                'dynamic' => $a['dynamic'] == 'true',
                'dhcp' => isset($a['dhcp']) ? $a['dhcp'] == 'true' : null,
                'disabled' => isset($a['disabled']) ? $a['disabled'] == 'true' : null,
                'published' => isset($a['published']) ? $a['published'] == 'true' : null,
                'invalid' => isset($a['published']) ? $a['invalid'] == 'true' : null,
                'comment' => isset($a['comment']) ? $a['comment'] : "",
                'vlan_id' => isset($vlans[$a['interface']]['vlan_id']) ? (int)$vlans[$a['interface']]['vlan_id']: -1,
                'status' => $status,
                'extra' => [
                    'id' => $a['.id'],
                    'interface_name' => $a['interface'],
                ]
            ];
        }
        $this->response = $arps;
        return $this;
    }
}
