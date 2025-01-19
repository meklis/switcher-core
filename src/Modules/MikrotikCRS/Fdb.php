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
        Helper::prepareFilter($filter);
        $fdb_port = $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        if ($filter['vlan_id']) {
            $fdb_port .= ".{$filter['vlan_id']}";
        }
        if ($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
        }
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->response = $this->formatResponse($this->snmp->walk([
                    Oid::init($fdb_port),
                ]));
                if ($this->response['dot1q.FdbPort']->error() && strpos($this->response['dot1q.FdbPort']->error(), "OID not increasing") !== false) {
                    throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
                }
                break;
            } catch (\Exception $e) {
                usleep(50000);
            }
        }
        return $this;
    }

    protected function formate()
    {
        if ($this->response) {
            $pretties = [];
            $ports = [];
            if ($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
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
