<?php


namespace SnmpSwitcher\Switcher\Parser\Errors;

use \SnmpSwitcher\Switcher\Parser\AbstractParser;


class DefaultParser extends AbstractParser
{
    protected function formate() {
        $in_err = !$this->response['if.InErrors']->error() ?  $this->response['if.InErrors']->fetchAll() : [];
        $out_err = !$this->response['if.OutErrors']->error() ?  $this->response['if.OutErrors']->fetchAll() : [];
        $in_disc = !$this->response['if.InDiscards']->error() ?  $this->response['if.InDiscards']->fetchAll() : [];
        $out_disc = !$this->response['if.OutDiscards']->error() ?  $this->response['if.OutDiscards']->fetchAll() : [];
        $indexes = $this->getIndexes();

        $errors = [];
        foreach ($in_err as $i) {
            $index = $this->getIndexByOid($i->getOid());
            $errors[$index]['port'] = $indexes[$index];
            $errors[$index]['in_errors'] = $i->getValue();
        }
        foreach ($out_err as $o) {
            $index = $this->getIndexByOid($o->getOid());
            $errors[$index]['out_errors'] = $o->getValue();
        }
        foreach ($in_disc as $o) {
            $index = $this->getIndexByOid($o->getOid());
            $errors[$index]['in_discards'] = $o->getValue();
        }
        foreach ($out_disc as $o) {
            $index = $this->getIndexByOid($o->getOid());
            $errors[$index]['out_discards'] = $o->getValue();
        }
        return array_values($errors);
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        $errors = [];
        foreach ($this->formate() as $num=>$val) {
            if($filter['port'] && $filter['port'] != $val['port']) {
                unset($errors[$num]);
            }
        }
       return array_values($errors);
    }
    public function walk($filter = [])
    {
        $oids = [
            $this->oidsCollector->getOidByName('if.InErrors')->getOid(),
            $this->oidsCollector->getOidByName('if.OutErrors')->getOid(),
            $this->oidsCollector->getOidByName('if.InDiscards')->getOid(),
            $this->oidsCollector->getOidByName('if.OutDiscards')->getOid(),
        ];
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