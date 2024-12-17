<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;
    protected function formate() {
        if($this->response) {
            $pretties = [];
            $statuses = [];
            $ports = [];
            if($this->response['dot1q.FdbStatus']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbStatus']->error()} from {$this->response['dot1q.FdbStatus']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbStatus']->fetchOne()) {
                    $data = Helper::oid2MacVlan($d->getOid());
                    $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
                }
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $data = Helper::oid2MacVlan($d->getOid());
                    $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
                }
            }
            foreach ($statuses as $key=>$status) {
                list($vlanId, $macAddr) = explode("-", $key);
                if(!isset($ports[$key])) {
                    continue;
                }
                if(!(int)$ports[$key])  continue;
                try {
                    $pretties[] = [
                        'interface' => $this->getIfaceByDot1q($ports[$key]),
                        'vlan_id' => (int)$vlanId,
                        'mac_address' => $macAddr,
                        'status' => $status,
                    ];
                } catch (\Throwable $e) {}
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
