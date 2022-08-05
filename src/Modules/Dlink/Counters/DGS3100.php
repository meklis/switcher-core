<?php


namespace SwitcherCore\Modules\Dlink\Counters;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;


class DGS3100 extends SwitchesPortAbstractModule
{
    protected function formate() {
        $indexes = $this->getIndexes();
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($indexes[$port_index])) continue;
                if(strpos($indexes[$port_index]['name'], 'ch') !== false) continue;
                $metric_name = str_replace(['if_', 'hc_'], '', Helper::fromCamelCase($oid_name));
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
        foreach ([
            $this->oids->getOidByName('if.HCInOctets'),
            $this->oids->getOidByName('if.HCOutOctets'),
                 ] as $oid) {
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