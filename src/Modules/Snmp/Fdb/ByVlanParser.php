<?php


namespace SwitcherCore\Modules\Snmp\Fdb;

use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ByVlanParser extends AbstractModule
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
            if($filter['port'] > $this->model->ports) {
                throw new \InvalidArgumentException("Not corrected port value. Max port value is {$this->model->ports}");
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

    public function walk($filter = [])
    {
       Helper::prepareFilter($filter);
       //Get vlans
       $this->response = $this->formatResponse($this->walker->walk([$this->oidsCollector->getOidByName('dot1q.VlanStaticName')->getOid()]));
       $vlanResponse = $this->getResponseByName('dot1q.VlanStaticName');
       if($vlanResponse->error()) {
           throw new IncompleteResponseException($vlanResponse->error());
       }
       $vlans = [];
       foreach ($vlanResponse->fetchAll() as $resp) {
           $vlans[] = Helper::getIndexByOid($resp->getOid());
       }

       $oids = [];
       $oids[] =  $this->oidsCollector->getOidByName('dot1q.FdbPort')->getOid();
       $oids[] =  $this->oidsCollector->getOidByName('dot1q.FdbStatus')->getOid();
       if($filter['vlan_id']) {
           $oids[0] .= ".{$filter['vlan_id']}";
           $oids[1] .= ".{$filter['vlan_id']}";
       } else {
           $old_oids = $oids;
           $oids = [];
           foreach ($vlans as $vlan) {
               $oids[] = $old_oids[0] . ".{$vlan}";
               $oids[] = $old_oids[1] . ".{$vlan}";
           }
       }
       if($filter['vlan_id'] && $filter['mac']) {
            $oids[0] .= "." . Helper::mac2oid($filter['mac']);
            $oids[1] .= "." .   Helper::mac2oid($filter['mac']);
       } elseif ($filter['mac']) {
           throw new \Exception("VlanID must be setted for mac filtering");
       }
       $this->response = $this->formatResponse($this->walker->walk($oids));
        return $this;
    }


}