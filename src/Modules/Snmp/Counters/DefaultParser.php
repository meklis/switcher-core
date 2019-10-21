<?php


namespace SwitcherCore\Modules\Snmp\Counters;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class DefaultParser extends AbstractModule
{
    protected function formate() {
        $indexes = $this->getIndexes();
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                if(!isset($indexes[$port_index])) continue;
                $metric_name = str_replace(['if_'], '', Helper::fromCamelCase($oid_name));
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

    public function run($params = [])
    {
        Helper::prepareFilter($params);
        $oids = [];
        foreach ($this->oidsCollector->getOidsByRegex('if\.HC.*') as $oid) {
            $oids[] = $oid->getOid();
        }

        if($params['port']) {
            $indexes = [];
            foreach ($this->getIndexes() as $index=>$port) {
                $indexes[$port] = $index;
            }
            foreach ($oids as $num=>$oid) {
                $oids[$num] .= ".{$indexes[$params['port']]}";
            }
        }
        $this->response = $this->formatResponse($this->walker->walk($oids));
        return $this;
    }
}