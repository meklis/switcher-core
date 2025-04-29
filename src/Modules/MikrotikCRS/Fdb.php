<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;

    public function run($filter = [])
    {
        try {
            $this->runWithDot1q();
        }  catch (\Exception $e) {
            $this->runWithDot1d();
        }
        return $this;
    }

    protected function runWithDot1q($filter = [])
    {
        Helper::prepareFilter($filter);
        $fdb_port = $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        if ($filter['vlan_id']) {
            $fdb_port .= ".{$filter['vlan_id']}";
        }
        if ($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
        }
        $this->snmp->setOidIncreasingCheck(false);
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($fdb_port),
        ]));
        $this->snmp->setOidIncreasingCheck(true);
        if ($this->response['dot1q.FdbPort']->error()) {
            throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
        }
    }

    protected function runWithDot1d()
    {
        $fdb_port = $this->oids->getOidByName('dot1d.FdbPort')->getOid();
        $this->snmp->setOidIncreasingCheck(false);
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($fdb_port),
        ]));
        $this->snmp->setOidIncreasingCheck(true);
        if ($this->response['dot1d.FdbPort']->error()) {
            throw new \Exception("Returned error {$this->response['dot1d.FdbPort']->error()} from {$this->response['dot1d.FdbPort']->getRaw()->ip}");
        }
        return $this;
    }

    protected function formate()
    {
        if ($this->response) {
            $response = array_values($this->response)[0];
            $pretties = [];
            $ports = [];
            if ($response->error()) {
                throw new \Exception("Returned error {$response->error()} from {$response->getRaw()->ip}");
            } else {
                while ($d = $response->fetchOne()) {
                    $data = Helper::oid2MacVlan($d->getOid());
                    $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
                }
            }
            foreach ($ports as $key => $status) {
                list($vlanId, $macAddr) = explode("-", $key);
                if (!isset($ports[$key])) {
                    continue;
                }
                if (!$vlanId) {
                    continue;
                }
                if (!(int)$ports[$key]) continue;
                try {
                    $iface = $this->getIfaceByDot1q($ports[$key]);
                    if (!$iface) {
                        continue;
                    }
                    $pretties[] = [
                        'interface' => $iface,
                        'vlan_id' => (int)$vlanId,
                        'mac_address' => $macAddr,
                        'status' => null,
                    ];
                } catch (\Throwable $e) {
                }
            }
            return $pretties;
        } else {
            throw new \Exception("No response");
        }
    }

    protected function getIfaceByDot1q($ident)
    {
        $filtered = array_filter($this->getInterfacesIds(), function ($iface) use ($ident) {
            return $iface['_dot1q_id'] == $ident;
        });
        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        return null;
    }
}
