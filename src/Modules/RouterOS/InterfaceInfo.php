<?php


namespace SwitcherCore\Modules\RouterOS;



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
        $resp = [];
        $formatedResp = [];
        if(isset($params['name']) && $params['name']) {
            $formatedResp['?name'] = $params['name'];
        }
        foreach ($this->execComm('/interface/print', $formatedResp) as $iface) {
            $resp[] = [
              '_id' =>  isset($iface['.id']) ? $iface['.id'] : null,
              'name' =>  isset($iface['name']) ? $iface['name'] : null,
              'type' =>  isset($iface['type']) ? $iface['type'] : null,
              'mtu' =>  isset($iface['mtu']) ? $iface['mtu'] : null,
              'actual_mtu' =>  isset($iface['actual-mtu']) ? $iface['actual-mtu'] : null,
              'l2mtu' =>  isset($iface['l2mtu']) ? $iface['l2mtu'] : null,
              'mac_address' =>  isset($iface['mac-address']) ? $iface['mac-address'] : null,
              'last_link_up_time' =>  isset($iface['last-link-up-time']) ? $iface['last-link-up-time'] : null,
              'link_downs' =>  isset($iface['link-downs']) ? $iface['link-downs'] : null,
              'rx_byte' =>  isset($iface['rx-byte']) ? $iface['rx-byte'] : null,
              'tx_byte' =>  isset($iface['tx-byte']) ? $iface['tx-byte'] : null,
              'rx_packet' =>  isset($iface['rx-packet']) ? $iface['rx-packet'] : null,
              'tx_packet' =>  isset($iface['tx-packet']) ? $iface['tx-packet'] : null,
              'rx_drop' =>  isset($iface['rx-drop']) ? $iface['rx-drop'] : null,
              'tx_drop' =>  isset($iface['tx-drop']) ? $iface['tx-drop'] : null,
              'running' =>  isset($iface['running']) ?  $iface['running'] == 'true' : null,
              'disabled' =>  isset($iface['disabled']) ? $iface['disabled'] == 'true'  : null,
            ];
        }
        $this->response = $resp;
        return $this;
    }
}