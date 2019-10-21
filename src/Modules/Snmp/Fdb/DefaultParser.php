<?php


namespace SwitcherCore\Modules\Snmp\Fdb;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class DefaultParser extends AbstractModule
{
    protected function formate() {
        if($this->response) {
            $pretties = [];
            $statuses = [];
            $ports = [];
            if($this->response['dot1q.FdbStatus']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbStatus']->error()} from {$this->response['dot1q.FdbStatus']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbStatus']->fetchOne()) {
                   $data = Helper::oid2mac($d->getOid());
                   $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
                }
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $data = Helper::oid2mac($d->getOid());
                    $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
                }
            }
            foreach ($statuses as $key=>$status) {
                list($vlanId, $macAddr) = explode("-", $key);
                $pretties[] = [
                    'port' => $ports[$key],
                    'vlan_id' => $vlanId,
                    'mac' => $macAddr,
                    'status' => $status,
                ];
            }
            return $pretties;
        } else {
            throw new \Exception("No response");
        }
    }
    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['port']) {
            if($filter['port'] > $this->model->getPorts()) {
                throw new \InvalidArgumentException("Not corrected port value. Max port value is {$this->model->getPorts()}");
            }
            foreach ($formated as $num=>$fdb) {
                if($fdb['port'] != $filter['port']) {
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
                if($fdb['mac'] != $filter['mac']) {
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
       $fdb_port =  $this->oidsCollector->getOidByName('dot1q.FdbPort')->getOid();
       $fdb_status =  $this->oidsCollector->getOidByName('dot1q.FdbStatus')->getOid();
       if($filter['vlan_id']) {
           $fdb_port .= ".{$filter['vlan_id']}";
           $fdb_status .= ".{$filter['vlan_id']}";
       }
       if($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
            $fdb_status .= "." .   Helper::mac2oid($filter['mac']);
       } elseif ($filter['mac']) {
           throw new \Exception("VlanID must be setted for mac filtering");
       }
       $this->response = $this->formatResponse($this->walker->walkBulk([
            $fdb_status, $fdb_port,
       ]));
        return $this;
    }


}