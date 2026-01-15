<?php

namespace SwitcherCore\Modules\ZTE\C300Series;

use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Helper;

class Vlans extends ModuleAbstract {
    protected function formate() {
        $response = [];
        $names = $this->getResponseByName('zx.anVlanName');
        if ($names->error()) {
            throw new IncompleteResponseException($names->error());
        }
        $response = [];
        foreach ($names->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['name'] = $resp->getValue();
            $response[Helper::getIndexByOid($resp->getOid())]['id'] = Helper::getIndexByOid($resp->getOid());
        }
        return array_values($response);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        return $this->formate();
    }

    public function run($filter = []) {
        Helper::prepareFilter($filter);
        $oids[] = $this->oids->getOidByName('zx.anVlanName')->getOid();
        if($filter['vlan_id']) {
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$filter['vlan_id']}";
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