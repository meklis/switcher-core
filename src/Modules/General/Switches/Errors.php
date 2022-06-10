<?php


namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


abstract class Errors extends AbstractInterfaces
{
    protected function formate() {
        $in_err = !$this->getResponseByName('if.InErrors')->error() ?  $this->getResponseByName('if.InErrors')->fetchAll() : [];
        $out_err = !$this->getResponseByName('if.OutErrors')->error() ? $this->getResponseByName('if.OutErrors')->fetchAll() : [];
        $in_disc = !$this->response['if.InDiscards']->error() ?  $this->response['if.InDiscards']->fetchAll() : [];
        $out_disc = !$this->response['if.OutDiscards']->error() ?  $this->response['if.OutDiscards']->fetchAll() : [];

        $indexes = [];
        foreach ($this->getInterfacesIds() as $id) {
            $indexes[$id['id']] = $id;
        }
        $errors = [];
        foreach ($in_err as $i) {
            $index = Helper::getIndexByOid($i->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['interface'] = $indexes[$index];
            $errors[$index]['in_errors'] = $i->getValue();
        }
        foreach ($out_err as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['interface'] = $indexes[$index];
            $errors[$index]['out_errors'] = $o->getValue();
        }
        foreach ($in_disc as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['interface'] = $indexes[$index];
            $errors[$index]['in_discards'] = $o->getValue();
        }
        foreach ($out_disc as $o) {
            $index = Helper::getIndexByOid($o->getOid());
            if(!isset($indexes[$index])) continue;
            $errors[$index]['interface'] = $indexes[$index];
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
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($errors as $num=>$val) {
                if($interface['id'] != $val['interface']['id']) {
                    unset($errors[$num]);
                }
            }
        }
       return array_values($errors);
    }
    public function run($filter = [])
    {
        $oids = [
            $this->oids->getOidByName('if.InErrors')->getOid(),
            $this->oids->getOidByName('if.OutErrors')->getOid(),
            $this->oids->getOidByName('if.InDiscards')->getOid(),
            $this->oids->getOidByName('if.OutDiscards')->getOid(),
        ];
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
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