<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class Rmon extends \SwitcherCore\Modules\General\Switches\Rmon {
    use InterfacesTrait;

    private $if_indexes = [];

    protected function formate() {
        $indexes = [];
        foreach ($this->getInterfacesIds() as $id) {
            $indexes[$id['_snmp_id']] = $id;
        }
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            if($wrappedResponse->error()) {
                //return [];
                throw new \Exception("Returned error {$wrappedResponse->error()} from {$wrappedResponse->getRaw()->ip}");
            }
        }
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($this->if_indexes[$port_index])) continue;
                $index = $this->if_indexes[$port_index];
                
                if(!isset($indexes[$index])) continue;
                $response[$index][str_replace('rmon_', '', Helper::fromCamelCase($oid_name))] = (int) $resp->getValue();
                $response[$index]['interface'] = $indexes[$index];
            }
        }
        
        return array_values($response);
    }
    
    public function run($filter = []) {
        Helper::prepareFilter($filter);

        $ports_if_indexes_oid =  $this->oids->getOidByName('dot1q.PortIfIndex')->getOid();
        $ports_if_indexes = $this->formatResponse($this->snmp->walk([ Oid::init($ports_if_indexes_oid) ]));
        $filter_by_index = false;
        while ($d = $ports_if_indexes['dot1q.PortIfIndex']->fetchOne()) {
            $index = Helper::getIndexByOid($d->getOid());
            $this->if_indexes[$index] = $d->getParsedValue();
            if($filter['interface'] == $d->getParsedValue()) $filter_by_index = $index;
        }

        $oids = [];
        foreach ($this->oids->getOidsByRegex('rmon.*') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($filter_by_index) {
            foreach ($oids as $num => $oid) {
                $oids[$num] .= ".{$filter_by_index}";
            }
        }

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
