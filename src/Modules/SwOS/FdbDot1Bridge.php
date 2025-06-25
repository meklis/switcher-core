<?php


namespace SwitcherCore\Modules\SwOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class FdbDot1Bridge extends \SwitcherCore\Modules\General\Switches\FdbDot1Bridge {
    use InterfacesTrait;
    
    protected function formate() {
        if($this->response) {
            $pretties = [];
            $data = [];
            if($this->response['dot1d.TpFdbPort']->error()) {
                throw new \Exception("Returned error {$this->response['dot1d.TpFdbPort']->error()} from {$this->response['dot1d.TpFdbPort']->getRaw()->ip}");
            }
            if($this->response['dot1d.TpFdbStatus']->error()) {
                throw new \Exception("Returned error {$this->response['dot1d.TpFdbStatus']->error()} from {$this->response['dot1d.TpFdbStatus']->getRaw()->ip}");
            }
            while ($d = $this->response['dot1d.TpFdbPort']->fetchOne()) {
                $mac = Helper::oid2mac($d->getOid());
                foreach($this->response['dot1d.TpFdbStatus']->fetchAll() as $oidStatus) {
                    if($mac === Helper::oid2mac($oidStatus->getOid())) {
                        $data[$d->getParsedValue()][$mac] = $oidStatus->getParsedValue();
                        break;
                    }
                }
            }
            foreach ($data as $port => $arr) {
                foreach($arr as $mac => $status) {
                    try {
                        $pretties[] = [
                            'interface' => $this->parseInterface($port),
                            'vlan_id' => 0,
                            'mac_address' => $mac,
                            'status' => $status,
                        ];
                    } catch (\Throwable $e) {}
                }
            }
            return $pretties;
        } else {
            throw new \Exception("No response");
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

    public function run($filter = []) {
        Helper::prepareFilter($filter);
        $oidTpFdbPort = $this->oids->getOidByName('dot1d.TpFdbPort')->getOid();
        $oidTpFdbStatus = $this->oids->getOidByName('dot1d.TpFdbStatus')->getOid();
        if($filter['mac']) {
            $oidTpFdbPort .= "." . Helper::mac2oid($filter['mac']);
            $oidTpFdbStatus .= "." . Helper::mac2oid($filter['mac']);
        }

       $this->response = $this->formatResponse($this->snmp->walkBulk([
            Oid::init($oidTpFdbPort), Oid::init($oidTpFdbStatus), 
       ]));
        return $this;
    }


}
