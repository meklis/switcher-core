<?php

namespace SwitcherCore\Modules\HpSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
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
                    $mac = Helper::oid2mac($d->getOid());
                    $statuses[$mac] = $d->getParsedValue();
                }
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $mac = Helper::oid2mac($d->getOid());
                    $ports[$mac] = $d->getValue();
                }
            }
            foreach ($statuses as $mac=>$status) {
                if(!isset($ports[$mac])) {
                    continue;
                }
                if(!(int)$ports[$mac])  continue;
                try {
                    $pretties[] = [
                        'interface' => $this->parseInterface($ports[$mac], 'id'),
                        'vlan_id' => null,
                        'mac_address' => $mac,
                        'status' => $status,
                    ];
                } catch (\Throwable $e) {}
            }
            return $pretties;
        } else {
            throw new \Exception("No response");
        }
    }
}
