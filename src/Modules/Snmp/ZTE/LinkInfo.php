<?php


namespace SwitcherCore\Modules\Snmp\ZTE;

use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\Snmp\Link\DefaultParser;

class LinkInfo extends ZteAbstractModule

{

    protected function formate()
    {
        $snmp_high_speed = $this->getResponseByName('if.HighSpeed')->fetchAll();
        $snmp_type = $this->getResponseByName('if.Type')->fetchAll();
        $snmp_oper_status = $this->getResponseByName('if.OperStatus')->fetchAll();
        $snmp_admin_status = $this->getResponseByName('if.AdminStatus')->fetchAll();
        $descr = $this->getResponseByName('if.Alias')->fetchAll();
        $names = $this->getResponseByName('if.Name')->fetchAll();

        foreach ($this->getIndexes() as $index => $name) {
            $indexes[$index]['name'] = null;
            $indexes[$index]['port'] = "{$name}";
            $indexes[$index]['medium_type'] = null;
            $indexes[$index]['address_learning'] = null;
            $indexes[$index]['description'] = null;
            $indexes[$index]['oper_status'] = null;
            $indexes[$index]['nway_status'] = null;
            $indexes[$index]['admin_state'] = null;
            $indexes[$index]['admin_state'] = null;
            $indexes[$index]['last_change'] = null;
            $indexes[$index]['id'] = $index;
        }

        foreach ($snmp_high_speed as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] =  $index->getValue();
        }
        foreach ($descr as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['description'] =  $index->getValue();
        }
        foreach ($snmp_type as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['type'] =  $index->getParsedValue();
        }


        foreach ($snmp_oper_status as $index) {
           $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] =  $index->getParsedValue();
        }
        foreach ($snmp_admin_status as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] =  $index->getParsedValue();
        }
        foreach ($names as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['name'] =  $index->getParsedValue();
        }
        foreach ($indexes as $index => $v) {
            if($v['name'] == null) {
                unset($indexes[$index]);
            }
        }
        return $indexes;
    }
    public function run($filter = [])
    {
        if($filter['port']) {
            $index = $this->getIndexByPort($filter['port']);
            $data = [
                $this->obj->oidCollector->getOidByName('if.HighSpeed')->getOid() . "." . $index,
                $this->obj->oidCollector->getOidByName('if.Name')->getOid() . "." . $index,
                $this->obj->oidCollector->getOidByName('if.Type')->getOid() . "." . $index,
                $this->obj->oidCollector->getOidByName('if.OperStatus')->getOid() . "." . $index,
                $this->obj->oidCollector->getOidByName('if.AdminStatus')->getOid() . "." . $index,
                $this->obj->oidCollector->getOidByName('if.Alias')->getOid() . "." . $index,
            ];
            $this->response = $this->formatResponse($this->obj->walker->walk($data));
        } else {
            $data = [
                $this->obj->oidCollector->getOidByName('if.HighSpeed')->getOid(),
                $this->obj->oidCollector->getOidByName('if.Name')->getOid(),
                $this->obj->oidCollector->getOidByName('if.Type')->getOid(),
                $this->obj->oidCollector->getOidByName('if.OperStatus')->getOid(),
                $this->obj->oidCollector->getOidByName('if.AdminStatus')->getOid(),
                $this->obj->oidCollector->getOidByName('if.Alias')->getOid(),
            ];
            $this->response = $this->formatResponse($this->obj->walker->walk($data));
        }

        return $this;
    }

    public function getPretty()
    {
        return $this->formate();
    }

    public function getPrettyFiltered($filter = [])
    {
        return array_values($this->formate());
    }
}