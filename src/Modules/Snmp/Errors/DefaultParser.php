<?php


namespace SwitcherCore\Modules\Snmp\Errors;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class DefaultParser extends AbstractModule
{
    protected function formate() {
        $in_err = !$this->getResponseByName('if.InErrors')->error() ?  $this->getResponseByName('if.InErrors')->fetchAll() : [];
        $out_err = !$this->getResponseByName('if.OutErrors')->error() ? $this->getResponseByName('if.OutErrors')->fetchAll() : [];
        $in_disc = !$this->response['if.InDiscards']->error() ?  $this->response['if.InDiscards']->fetchAll() : [];
        $out_disc = !$this->response['if.OutDiscards']->error() ?  $this->response['if.OutDiscards']->fetchAll() : [];
        $indexes = $this->getIndexes();
        $errors = [];
        foreach ($in_err as $i) {
            $index = Helper::getIndexByOid($i->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['port'] = $indexes[$index];
            $errors[$index]['in_errors'] = $i->getValue();
        }
        foreach ($out_err as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['out_errors'] = $o->getValue();
        }
        foreach ($in_disc as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['in_discards'] = $o->getValue();
        }
        foreach ($out_disc as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
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
        $errors = $this->formate();
        foreach ($errors as $num=>$val) {
            if($filter['port'] && $filter['port'] != $val['port']) {
                unset($errors[$num]);
            }
        }
       return array_values($errors);
    }
    public function run($filter = [])
    {
        $oids = [
            $this->obj->oidCollector->getOidByName('if.InErrors')->getOid(),
            $this->obj->oidCollector->getOidByName('if.OutErrors')->getOid(),
            $this->obj->oidCollector->getOidByName('if.InDiscards')->getOid(),
            $this->obj->oidCollector->getOidByName('if.OutDiscards')->getOid(),
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
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }

        $this->response = $this->formatResponse($this->obj->walker->walk($oidObjects));
        return $this;
    }
}