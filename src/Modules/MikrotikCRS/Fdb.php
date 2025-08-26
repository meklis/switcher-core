<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\InterfaceNotFound;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

use RouterosAPI;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;


    protected $api;

    function __construct(RouterosAPI $api)
    {
        $this->api = $api;
    }

    public function run($filter = [])
    {
        try {
            $this->response = $this->runOverApi($filter);
            return $this;
        } catch (InterfaceNotFound $e) {
            throw $e;
        } catch (\Exception $e) {
        }
        try {
            $response = $this->runWithDot1d();
            $this->response = $this->prettySnmpResponse($response);
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected $_fdb = [];

    protected function runOverApi($filter = [])
    {
        if (!$filter && $this->_fdb) {
            return $this->_fdb;
        }
        $params = [];
        if($filter['interface']) {
            $params['?interface'] = $this->parseInterface($filter['interface'])['name'];
        }
        if($filter['mac']) {
            $params['?mac-address'] = Helper::formatMac($filter['mac']);
        }
        // if(isset($params['vlan_id']) && $params['vlan_id']) {
        //     $filter['?vid'] = $params['vlan_id'];
        // }
        $resp = $this->api->comm("/interface/bridge/host/print", $params);
        if (!$resp) {
            return [];
        } elseif (isset($resp['!trap'][0]['message'])) {
            throw  new \Exception("RouterOS api returned error - " . $resp['!trap'][0]['message']);
        }
        $pretties = [];
        foreach ($resp as $item) {
            $iface = $this->parseInterface($item['on-interface'], 'name');
            $pretties[] = [
                'interface' => $iface,
                'vlan_id' => isset($item['vid']) ? (int)$item['vid'] : 0,
                'mac_address' => $item['mac-address'],
                'status' => null,
            ];
        }
        $this->_fdb = $pretties;
        return $pretties;
    }


    protected function runWithDot1d()
    {
        $fdb_port = $this->oids->getOidByName('dot1d.TpFdbPort')->getOid();
        $this->snmp->setOidIncreasingCheck(false);
        $response = $this->formatResponse($this->snmp->walk([
            Oid::init($fdb_port),
        ]));
        $this->snmp->setOidIncreasingCheck(true);
        if ($response['dot1d.TpFdbPort']->error()) {
            throw new \Exception("Returned error {$response['dot1d.TpFdbPort']->error()} from {$response['dot1d.TpFdbPort']->getRaw()->ip}");
        }
        return $response;
    }

    protected function formate()
    {
        return $this->response;
    }

    protected function prettySnmpResponse($response)
    {
        $oidName = array_keys($response)[0];
        $response = array_values($response)[0];
        $pretties = [];
        if ($response->error()) {
            throw new \Exception("Returned error {$response->error()} from {$response->getRaw()->ip}");
        }
        while ($d = $response->fetchOne()) {
            try {
                $iface = $this->parseInterface($d->getValue(), '_snmp_id');
                $data = Helper::oid2mac($d->getOid());
                $pretties[] = [
                    'interface' => $iface,
                    'vlan_id' => 0,
                    'mac_address' => $data,
                    'status' => null,
                ];
            } catch (\Throwable $e) {

            }
        }
        return $pretties;
    }


}
