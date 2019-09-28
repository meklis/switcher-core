<?php


namespace SnmpSwitcher\Switcher\Parser\Fdb;

use \SnmpSwitcher\Switcher\Parser\AbstractParser;

class DefaultParser extends AbstractParser
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
                   $data = $this->oid2mac($d->getOid());
                   $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
                }
            }
            if($this->response['dot1q.FdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1q.FdbPort']->error()} from {$this->response['dot1q.FdbPort']->getRaw()->ip}");
            } else {
                while ($d = $this->response['dot1q.FdbPort']->fetchOne()) {
                    $data = $this->oid2mac($d->getOid());
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
        $this->prepareFilter($filter);
        $formated = $this->formate();
        if($filter['port']) {
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
       $this->prepareFilter($filter);
       $fdb_port =  $this->oidsCollector->getOidByName('dot1q.FdbPort')->getOid();
       $fdb_status =  $this->oidsCollector->getOidByName('dot1q.FdbStatus')->getOid();
       if($filter['vlan_id']) {
           $fdb_port .= ".{$filter['vlan_id']}";
           $fdb_status .= ".{$filter['vlan_id']}";
       }
       if($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . $this->mac2oid($filter['mac']);
            $fdb_status .= "." .   $this->mac2oid($filter['mac']);
       } elseif ($filter['mac']) {
           throw new \Exception("VlanID must be setted for mac filtering");
       }
       $this->response = $this->formatResponse($this->walker->walk([
            $fdb_status, $fdb_port,
       ]));
        return $this;
    }

    protected function oid2mac($oid) {
        $count = substr_count($oid, '.');
        $dec= explode('.', $oid);
        $vid = $dec[$count-6];
        if(strlen(dechex($dec[$count-5])) == 1) $m1 = '0'.dechex($dec[$count-5]); else $m1=dechex($dec[$count-5]);
        if(strlen(dechex($dec[$count-4])) == 1) $m2 = '0'.dechex($dec[$count-4]); else $m2=dechex($dec[$count-4]);
        if(strlen(dechex($dec[$count-3])) == 1) $m3 = '0'.dechex($dec[$count-3]); else $m3=dechex($dec[$count-3]);
        if(strlen(dechex($dec[$count-2])) == 1) $m4 = '0'.dechex($dec[$count-2]); else $m4=dechex($dec[$count-2]);
        if(strlen(dechex($dec[$count-1])) == 1) $m5 = '0'.dechex($dec[$count-1]); else $m5=dechex($dec[$count-1]);
        if(strlen(dechex($dec[$count])) == 1) $m6 = '0'.dechex($dec[$count]); else $m6=dechex($dec[$count]);

        return ['mac'=>strtoupper("$m1:$m2:$m3:$m4:$m5:$m6"), 'vid'=>$vid];
    }
    protected function mac2oid($mac) {
        $mac = explode(":",strtoupper(str_replace(["-"," ","."],":",$mac)));
        $RESP = "";
        foreach ($mac as $otket) {
            $RESP .= ".".hexdec($otket);
        }
        return trim($RESP,".");
    }
}