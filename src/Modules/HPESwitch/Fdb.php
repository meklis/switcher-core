<?php

namespace SwitcherCore\Modules\HPESwitch;

use SnmpWrapper\Oid;
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
                throw new Exception("Returned error {$this->response['dot1q.FdbStatus']->error()} from {$this->response['dot1q.FdbStatus']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbStatus']->fetchOne()) {
                    $data = Helper::oid2MacVlan($d->getOid());
                    $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
                }
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
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
                        'interface' => $this->parseInterface($ports[$key], 'physical_id'),
                        'vlan_id' => (int)$vlanId,
                        'mac_address' => $macAddr,
                        'status' => $status,
                    ];
                } catch (\Throwable $e) {}
            }
            return $pretties;
        } else {
            throw new Exception("No response");
        }
    }
    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$fdb) {
                if($fdb['interface']['id'] != $interface['id']) {
                    unset($formated[$num]);
                }
            }
        }
        if($filter['vlan_id']) {
            foreach ($formated as $num=>$fdb) {
                if($fdb['vlan_id'] != $filter['vlan_id']) {
                    unset($formated[$num]);
                }
            }
        }
        if($filter['mac']) {
            foreach ($formated as $num=>$fdb) {
                if($fdb['mac_address'] != $filter['mac']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }
    function getPretty()
    {
        return $this->formate();
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $fdb_port =  $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        $fdb_status =  $this->oids->getOidByName('dot1q.FdbStatus')->getOid();
        if($filter['vlan_id']) {
            $fdb_port .= ".{$filter['vlan_id']}";
            $fdb_status .= ".{$filter['vlan_id']}";
        }
        if($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
            $fdb_status .= "." .   Helper::mac2oid($filter['mac']);
        }
        $this->response = $this->formatResponse($this->snmp->walkBulk([
            Oid::init($fdb_status), Oid::init($fdb_port),
        ]));
        return $this;
    }
}
