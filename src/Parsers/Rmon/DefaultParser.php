<?php


namespace SwitcherCore\Parsers\Rmon;

use \SwitcherCore\Parsers\AbstractParser;
use \SwitcherCore\Parsers\Helper;

class DefaultParser extends AbstractParser
{
    protected function formate() {
        $indexes = $this->getIndexes();
        $response = [];
        foreach ($this->response as $oid_name => $wrappedResponse) {
            foreach ($wrappedResponse->fetchAll() as $resp) {
                $port_index = Helper::getIndexByOid($resp->getOid());
                $response[$port_index][str_replace('rmon_', '', Helper::fromCamelCase($oid_name))] = $resp->getValue();
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

    public function walk($filter = [])
    {
        $oids = [];
        foreach ($this->oidsCollector->getOidsByRegex('rmon.*') as $oid) {
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
        $this->response = $this->formatResponse($this->walker->walk($oids));
        return $this;
    }
}