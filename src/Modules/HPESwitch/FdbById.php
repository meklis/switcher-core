<?php

namespace SwitcherCore\Modules\HPESwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class FdbById extends Fdb {
    use InterfacesTrait;

    private $fdb_id = [];

    protected function formate() {
        if($this->response) {
            $pretties = [];
            $statuses = [];
            $ports = [];
            if($this->response['dot1q.FdbStatus']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbStatus']->error()} from {$this->response['dot1q.FdbStatus']->getRaw()->ip}");
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            }
            while ($d = $this->response['dot1q.FdbStatus']->fetchOne()) {
                $data = Helper::oid2MacVlan($d->getOid());
                $data['vid'] = (isset($this->fdb_id[$data['vid']])) ? $this->fdb_id[$data['vid']] : '0';
                $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
            }
            while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                $data = Helper::oid2MacVlan($d->getOid());
                $data['vid'] = (isset($this->fdb_id[$data['vid']])) ? $this->fdb_id[$data['vid']] : '0';
                $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
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
            throw new \Exception("No response");
        }
    }

    public function run($filter = []) {
        Helper::prepareFilter($filter);
        $fdb_port =  $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        $fdb_status =  $this->oids->getOidByName('dot1q.FdbStatus')->getOid();
        $fdb_id_oid = $this->oids->getOidByName('dot1q.VlanFdbId')->getOid();

        if($filter['vlan_id']) $fdb_id_oid .= ".{$filter['vlan_id']}";
        $data = $this->formatResponse($this->snmp->walk([Oid::init($fdb_id_oid)]));

        if($data['dot1q.VlanFdbId']->error()) {
            throw new \Exception("Returned error {$data['dot1q.VlanFdbId']->error()} from {$data['dot1q.VlanFdbId']->getRaw()->ip}");
        }

        $fdb_id = [];
        $filter_fdb_id = false;
        while ($d = $data['dot1q.VlanFdbId']->fetchOne()) {
            $vid = Helper::getIndexByOid($d->getOid());
            $fdb_id[$d->getParsedValue()] = $vid;
            if($filter['vlan_id'] == $vid) $filter_fdb_id = $d->getParsedValue();
        }
        if($filter_fdb_id) {
            $fdb_port .= ".{$filter_fdb_id}";
            $fdb_status .= ".{$filter_fdb_id}";
        }
        if($filter_fdb_id && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
            $fdb_status .= "." .   Helper::mac2oid($filter['mac']);
        }
        
        $this->fdb_id = $fdb_id;

        $arr = [Oid::init($fdb_status), Oid::init($fdb_port)];
        $this->response = $this->formatResponse($this->snmp->walkBulk($arr));
        return $this;
    }
}
