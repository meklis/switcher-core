<?php


namespace SwitcherCore\Modules\Dlink\Vlan;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class PvidParser extends SwitchesPortAbstractModule
{
    protected function formate() {
        $indexes = $this->getIndexes();
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                $metric_name = str_replace(['dot1q_'], '', Helper::fromCamelCase($oid_name));
                $response[$port_index][$metric_name] = $resp->getValue();
                $response[$port_index]['port'] = $indexes[$port_index];
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
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['port']) {
            foreach ($formated as $num=>$val) {
                if($filter['port'] != $val['port']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($filter = [])
    {
        $oids = [];
        foreach ($this->oids->getOidsByRegex('dot1q.Pvid') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($filter['port']) {
            $indexes = [];
            foreach ($this->getIndexes() as $index=>$port) {
                $indexes[$port] = $index;
            }
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$indexes[$filter['port']]}";
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