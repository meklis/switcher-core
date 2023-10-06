<?php


namespace SwitcherCore\Modules\Dlink\Fdb;

use Exception;
use InvalidArgumentException;
use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class ByVlanParser extends SwitchesPortAbstractModule
{
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
                if(!(int)$ports[$key])  continue;
                $pretties[] = [
                    'interface' => $this->parseInterface($ports[$key]),
                    'vlan_id' => $vlanId,
                    'mac_address' => $macAddr,
                    'status' => $status,
                ];
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
        if($filter['mac_address']) {
            foreach ($formated as $num=>$fdb) {
                if($fdb['mac_address'] != $filter['mac_address']) {
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
       //Get vlans
       $this->response = $this->formatResponse($this->snmp->walk([Oid::init($this->oids->getOidByName('dot1q.VlanStaticName')->getOid())]));
       $vlanResponse = $this->getResponseByName('dot1q.VlanStaticName');
       if($vlanResponse->error()) {
           throw new IncompleteResponseException($vlanResponse->error());
       }
       $vlans = [];
       foreach ($vlanResponse->fetchAll() as $resp) {
           $vlans[] = Helper::getIndexByOid($resp->getOid());
       }

       $oids = [];
       $oids[] =  $this->oids->getOidByName('dot1q.FdbPort')->getOid();
       $oids[] =  $this->oids->getOidByName('dot1q.FdbStatus')->getOid();
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
       if($filter['vlan_id'] && $filter['mac_address']) {
            $oids[0] .= "." . Helper::mac2oid($filter['mac_address']);
            $oids[1] .= "." .   Helper::mac2oid($filter['mac_address']);
       } elseif ($filter['mac_address']) {
           throw new Exception("VlanID must be setted for mac filtering");
       }
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
       $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }


}