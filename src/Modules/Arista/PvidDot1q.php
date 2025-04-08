<?php


namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class PvidDot1q extends \SwitcherCore\Modules\General\Switches\PvidDot1q {
    use InterfacesTrait;

    private $if_indexes;

    protected function formate() {
        $indexes = [];
        foreach ($this->getInterfacesIds() as $index) {
            $indexes[$index['_snmp_id']] = [
                'interface' => $index,
                'pvid' => null,
            ];
        }

        while ($d = $this->response['dot1q.Pvid']->fetchOne()) {
            $if_index = Helper::getIndexByOid($d->getOid());
            $if_id = isset($this->if_indexes[$if_index]) ? $this->if_indexes[$if_index] : $if_index;
            if(!isset($indexes[$if_id])) continue;
            $indexes[$if_id]['pvid'] = $d->getValue();
        }

        return array_values($indexes);
    }
    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$val) {
                if($interface['id'] != $val['interface']['id']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $ports_if_indexes_oid =  $this->oids->getOidByName('dot1q.PortIfIndex')->getOid();
        $ports_if_indexes = $this->formatResponse($this->snmp->walk([ Oid::init($ports_if_indexes_oid) ]));
        $this->if_indexes = [];
        $filter_by_index = false;
        while ($d = $ports_if_indexes['dot1q.PortIfIndex']->fetchOne()) {
            $index = Helper::getIndexByOid($d->getOid());
            $this->if_indexes[$index] = $d->getParsedValue();
            if($filter['interface'] == $d->getParsedValue()) $filter_by_index = $index;
        }

        $oid = $this->oids->getOidByName('dot1q.Pvid')->getOid();
        if($filter_by_index) {
            $oid .= ".{$filter_by_index}";
        }
        $this->response = $this->formatResponse($this->snmp->walk([ Oid::init($oid) ]));
        return $this;
    }
}