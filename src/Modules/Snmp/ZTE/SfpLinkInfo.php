<?php


namespace SwitcherCore\Modules\Snmp\ZTE;

use SwitcherCore\Modules\Snmp\ZTE\ZteAbstractModule;
use SwitcherCore\Modules\Helper;

class SfpLinkInfo extends ZteAbstractModule

{

    protected function formate()
    {
        $tx_powers = $this->getResponseByName('zx.olt.OpticalPowerTxCurrValue')->fetchAll();
        $voltages = $this->getResponseByName('zx.olt.OpticalSupplyVoltage')->fetchAll();
        $vendor_names = $this->getResponseByName('zx.olt.OpticalVenderName')->fetchAll();
        $vendor_pns= $this->getResponseByName('zx.olt.OpticalVenderPn')->fetchAll();
        $module_types  = $this->getResponseByName('zx.olt.OpticalModuleType')->fetchAll();
        foreach ($this->getIndexes() as $index=>$name) {
            $indexes[$index]['port'] = "{$name}";
            $indexes[$index]['power_tx_value'] = null;
            $indexes[$index]['voltage'] = null;
            $indexes[$index]['vendor_name'] = null;
            $indexes[$index]['vendor_pn'] = null;
            $indexes[$index]['id'] = $index;
        }
        //@TODO Convert TX powers
        foreach ($tx_powers as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['power_tx_value'] =  $index->getValue();
        }
        //@TODO Convert Voltages
        foreach ($voltages as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['voltage'] =  $index->getValue();
        }

        foreach ($voltages as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['voltage'] =  $index->getValue();
        }

        foreach ($vendor_names as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['vendor_name'] =  $index->getParsedValue();
        }
        foreach ($module_types as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['module_type'] =  $index->getParsedValue();
        }
        foreach ($vendor_pns as $index) {
            $indexes[Helper::getIndexByOid($index->getOid())]['vendor_pn'] =  $index->getParsedValue();
        }
        $indexes = array_filter($indexes, function ($e) {
            if($e['vendor_name']) return true;
            return false;
        });
        return $indexes;
    }
    public function run($filter = [])
    {
        $add = "";
        if($filter['port']) {
            $add = ".{$this->getIndexByPort($filter['port'])}";
        }
        $data = [
            $this->oidsCollector->getOidByName('zx.olt.OpticalPowerTxCurrValue')->getOid() . $add,
            $this->oidsCollector->getOidByName('zx.olt.OpticalSupplyVoltage')->getOid(). $add,
            $this->oidsCollector->getOidByName('zx.olt.OpticalVenderName')->getOid(). $add,
            $this->oidsCollector->getOidByName('zx.olt.OpticalVenderPn')->getOid(). $add,
            $this->oidsCollector->getOidByName('zx.olt.OpticalModuleType')->getOid(). $add,
        ];

        $this->response = $this->formatResponse($this->walker->walk($data));
        return $this;
    }

    public function getPretty()
    {
        // TODO: Implement getPretty() method.
    }

    public function getPrettyFiltered($filter = [])
    {
       return  array_values($this->formate());
    }
}