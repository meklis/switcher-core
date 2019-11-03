<?php


namespace SwitcherCore\Modules\Snmp\ZTE;

use SwitcherCore\Modules\Helper;
class Fdb extends ZteAbstractModule
{
    protected $index_bias = 17301504;
    protected function formate() {
        if($this->response) {
            $pretties = [];
            $statuses = [];
            $ports = [];

            $data = $this->getResponseByName('zx.onu.MacForwardingTable');
            $pretties = [];
            foreach ($data->fetchAll() as $d) {
                $p = $this->getPortByIndex(Helper::getIndexByOid($d->getOid() , 7) + $this->index_bias) ;
                $port = $p['onu'] < 0 ? "{$p['shelf']}/{$p['slot']}/{$p['olt']}": "{$p['shelf']}/{$p['slot']}/{$p['olt']}:{$p['onu']}";
                if(!$p['olt']) continue;
                if(!$p['shelf']) continue;
                if(!$p['slot']) continue;
                $pretties[] = [
                    'port' => $port,
                    'vlan_id' => Helper::getIndexByOid($d->getOid(), 6),
                    'mac' => Helper::oid2macArray([
                        Helper::getIndexByOid($d->getOid(), 5),
                        Helper::getIndexByOid($d->getOid(), 4),
                        Helper::getIndexByOid($d->getOid(), 3),
                        Helper::getIndexByOid($d->getOid(), 2),
                        Helper::getIndexByOid($d->getOid(), 1),
                        Helper::getIndexByOid($d->getOid()),
                    ]),
                    'status' => $d->getParsedValue(),
                    'port_id' => Helper::getIndexByOid($d->getOid(), 7),
                    'oid' => $d->getOid(),
                ];
            }

            return $pretties;
        } else {
            throw new \Exception("No response");
        }
    }
    function getPrettyFiltered($filter = []) {
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
            $filter['mac'] = Helper::formatMac($filter['mac']);
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
       $fdb_status =  $this->oidsCollector->getOidByName('zx.onu.MacForwardingTable')->getOid();
       if($filter['port']) {
           $fdb_status .= "." . ($this->getIndexByPort($filter['port'], 'onu') - $this->index_bias);
       }
       if($filter['vlan_id'] && $filter['port']) {
           $fdb_status .= "." . $filter['vlan_id'];
       }

       if($filter['vlan_id'] && $filter['port'] && $filter['mac']) {
           $filter['mac'] = Helper::formatMac($filter['mac']);
           $fdb_status .= "." . Helper::mac2oid($filter['mac']);
       }

       $this->response = $this->formatResponse($this->walker->walkBulk([
            $fdb_status,
       ]));
        return $this;
    }


}