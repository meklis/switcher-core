<?php


namespace SwitcherCore\Modules\RouterOS;


use Exception;
use Khill\Duration\Duration;

class LeaseInfo extends ExecCommand
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

    protected $dhcpServers = [];
    private function getDhcpServerByParam($params) {
        if(isset($this->dhcpServers[json_encode($params)])) {
            return  $this->dhcpServers[json_encode($params)];
        }
        foreach ($this->getModule('dhcp_server_info')->run($params)->getPrettyFiltered() as $vl) {
                $this->dhcpServers[json_encode($params)] = $vl;
                return $vl;
        }
        throw new Exception("Dhcp server not found");
    }
    public function run($params = [])
    {
        $filter = [];
        if(isset($params['ip']) && $params['ip']) {
          $filter['?address'] = $params['ip'];
        }
        if(isset($params['mac']) && $params['mac']) {
          $filter['?mac-address'] = $params['mac'];
        }
        if(
            (isset($params['vlan_id']) && $params['vlan_id']) ||
            (isset($params['vlan_name']) && $params['vlan_name'])
        ) {
            $filter['?server'] = $this->getDhcpServerByParam([
                'vlan_id' => (int)$params['vlan_id'],
                'vlan_name' => $params['vlan_name'],
            ])['name'];
        }

        if(isset($params['dhcp_server']) && $params['dhcp_server']) {
            $filter['?server'] = $params['dhcp_server'];
        }
        $response = [];
        foreach ($this->execComm('/ip/dhcp-server/lease/print', $filter) as $a) {
            $response[] = [
                '_id' => $a['.id'],
                'host_name' => isset($a['host-name']) ? $a['host-name'] : '',
                'ip' => $a['address'],
                'mac' => $a['mac-address'],
                'status' => $a['status'],
                'radius' => $a['radius'] == 'true',
                'dynamic' => $a['dynamic'] == 'true',
                'blocked' => $a['blocked'] == 'true',
                'disabled' => $a['disabled'] == 'true',
                'lease_time' => isset($a['lease-time']) ? $a['lease-time'] : null ,
                'last_seen' => $a['last-seen'],
                'expires_at' => isset($a['expires-after']) ? (new Duration($a['expires-after']))->toSeconds() + time() : "-1",
                'server' => isset($a['server']) ? $a['server'] : null,
                'extra' => [
                    'id' => $a['.id'],
                    'client_id' => isset($a['client-id']) ? $a['client-id'] : null,
                    'server' => isset($params['hide_server_detail']) && $params['hide_server_detail'] == 'yes' ? null : $this->getDhcpServerByParam(['name' => $a['server']]),
                ]
            ];
        }
        $this->response = $response;
        return $this;
    }
}
