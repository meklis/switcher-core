<?php

namespace SwitcherCore\Modules\HuaweiQuidwayS93;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class PvidDot1q extends \SwitcherCore\Modules\General\Switches\PvidDot1q
{
    use InterfacesTrait;

    protected function formate() {
        $indexes = [];
        foreach ($this->getInterfacesIds() as $index) {
            $indexes[$index['id']] = [
                'interface' => $index,
                'pvid' => null,
            ];
        }
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($indexes[$port_index])) continue;
                $indexes[$port_index]['pvid'] = (int)$resp->getValue();
            }
        }
        return array_values($indexes);
    }
    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$val) {
                if($interface['id'] != $val['port']) {
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

        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $indexes = [];
            foreach ($this->getInterfacesIds() as $index=>$port) {
                $indexes[$port] = $index;
            }
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$indexes[$interface['id']]}";
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
