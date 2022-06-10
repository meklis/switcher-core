<?php


namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


abstract class Counters extends AbstractInterfaces
{
    protected function formate() {
        $indexes = [];
        foreach ($this->getInterfacesIds() as $id) {
            $indexes[$id['id']] = $id;
        }
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($indexes[$port_index])) continue;
                $metric_name = str_replace(['if_'], '', Helper::fromCamelCase($oid_name));
                $response[$port_index][$metric_name] = $resp->getValue();
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

    public function run($params = [])
    {
        Helper::prepareFilter($params);
        $oids = [];
        foreach ($this->oids->getOidsByRegex('if\.HC.*') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$interface['id']}";
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