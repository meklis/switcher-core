<?php


namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class Rmon extends AbstractInterfaces
{
    protected function formate() {

        $indexes = [];
        foreach ($this->getInterfacesIds() as $id) {
            $indexes[$id['_snmp_id']] = $id;
        }
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            if($wrappedResponse->error()) {
                return  [];
            }
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($indexes[$port_index])) continue;
                $response[$port_index][str_replace('rmon_', '', Helper::fromCamelCase($oid_name))] = $resp->getValue();
                $response[$port_index]['interface'] = $indexes[$port_index];
            }
        }
        return array_values($response);
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        $formated = $this->formate();
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$val) {
                if($iface['id'] != $val['interface']['id']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($filter = [])
    {
        $oids = [];
        foreach ($this->oids->getOidsByRegex('rmon.*') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$iface['_snmp_id']}";
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