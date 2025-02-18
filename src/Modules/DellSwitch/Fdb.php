<?php

namespace SwitcherCore\Modules\DellSwitch;

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
        Helper::prepareFilter($filter);
        $fdb_port =  $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        if($filter['vlan_id']) {
            $fdb_port .= ".{$filter['vlan_id']}";
        }
        if($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
        }
        $this->response = $this->formatResponse($this->snmp->walkBulk([
           Oid::init($fdb_port),
        ]));
        return $this;
    }
    protected function formate() {
        if($this->response) {
            $pretties = [];
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $data = Helper::oid2MacVlan($d->getOid());
                    try {
                        $iface = $this->getIfaceByDot1q($d->getParsedValue());
                        if(!$iface) {
                            continue;
                        }
                        $pretties[] = [
                            'interface' => $iface,
                            'vlan_id' => (int)$data['vid'],
                            'mac_address' => $data['mac'],
                            'status' => null,
                        ];
                    } catch (\Throwable $e) {}

                }
            }
            return $pretties;
        } else {
            throw new \Exception("No response");
        }
    }

    protected function getIfaceByDot1q($ident) {
        $filtered = array_filter($this->getInterfacesIds(), function ($iface) use ($ident) {
            return $iface['_dot1q_id'] == $ident;
        });
        if(count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        return null;
    }
}
