<?php


namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;


abstract class Counters extends AbstractInterfaces
{
    protected function formate() {
        $response = [];
        foreach ($this->getInterfacesIds() as $key => $iface) {
            $response[$key] = [
               'interface' => $iface,
            ];
        }
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($response[$port_index])) continue;
                $metric_name = str_replace(['if_hc_'], '', Helper::fromCamelCase($oid_name));
                $response[$port_index][$metric_name] = (float)$resp->getValue();
            }
        }
        return array_values(array_filter($response, function ($e) {
           return isset($e['in_octets']) && isset($e['out_octets']);
        }));
    }
    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
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
        $oids = [];
        foreach ($this->oids->getOidsByRegex('if\.HC.*') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$interface['_snmp_id']}";
            }
        }

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }

        $this->snmp->setOidIncreasingCheck(false);
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        $this->snmp->setOidIncreasingCheck(true);
        return $this;
    }
}