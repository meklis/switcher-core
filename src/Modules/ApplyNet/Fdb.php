<?php

namespace SwitcherCore\Modules\ApplyNet;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;
    protected function formate() {
        if($this->response) {
            $ports = [];
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $mac = Helper::oid2mac($d->getOid());
                    try {
                        $iface = $this->parseInterface($d->getValue());
                        $ports[] = [
                            'interface' => $iface,
                            'vlan_id' => 0,
                            'mac_address' => $mac,
                            'status' => null ,
                        ];
                    } catch (\Exception $e) {}
                }
            }

            return $ports;
        } else {
            throw new \Exception("No response");
        }
    }
}
