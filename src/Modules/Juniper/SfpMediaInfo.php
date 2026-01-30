<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\Juniper\InterfacesTrait;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends \SwitcherCore\Modules\General\Switches\SfpMediaInfo {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);
        $interfaces = [];
        $filter_iface = false;
        if($params['interface']) { 
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['_snmp_id']] = $ifc;
            $filter_iface = $ifc['_snmp_id'];
        } else {
            $interfaces = $this->getInterfacesIds();
        }

        $oids = [ 
            Oid::init($this->oids->getOidByName('ent.physicalName')->getOid()),
            Oid::init($this->oids->getOidByName('ent.aliasMappingIdentifier')->getOid()),
        ];
        $res = $this->formatResponse($this->snmp->walk($oids));
        $sfp_ids = [];
        try {
            foreach($res['ent.physicalName']->fetchAll() as $oid) {
                $id = Helper::getIndexByOid($oid->getOid());
                if(preg_match('/sfp/i', $oid->getValue())) {
                    $sfp_ids[$id] = true;
                }
            }
        } catch (\Exception $e) {}
        
        $mapping_id = [];
        try {
            foreach($res['ent.aliasMappingIdentifier']->fetchAll() as $oid) {
                $id = Helper::getIndexByOid($oid->getOid(), 1);
                // $val = $oid->getValue();
                // $val = explode('.', $val);
                // $val = $val[count($val) - 1];
                $val = Helper::getIndexByOid($oid->getValue());
                if(!isset($sfp_ids[$id])) continue;
                if(!isset($interfaces[$val])) continue;
                $mapping_id[$val] = $id;
            }
        } catch (\Exception $e) {}
        
        $oids = [
            Oid::init($this->oids->getOidByName('ent.physicalSerialNum')->getOid() . (($filter_iface && isset($mapping_id[$filter_iface])) ? ".{$mapping_id[$filter_iface]}" : "")),
            Oid::init($this->oids->getOidByName('ent.physicalMfgName')->getOid() . (($filter_iface && isset($mapping_id[$filter_iface])) ? ".{$mapping_id[$filter_iface]}" : "")),
            Oid::init($this->oids->getOidByName('ent.physicalModelName')->getOid() . (($filter_iface && isset($mapping_id[$filter_iface])) ? ".{$mapping_id[$filter_iface]}" : "")),
        ];
        $res = $this->formatResponse($this->snmp->walk($oids));
        $sn = [];
        try {
            foreach($res['ent.physicalSerialNum']->fetchAll() as $oid) {
                $id = Helper::getIndexByOid($oid->getOid());
                if(isset($sfp_ids[$id]) && in_array($id, $mapping_id) && $oid->getValue() !== '') {
                    $sn[$id] = $oid->getValue();
                }
            }
        } catch (\Exception $e) {}

        $vendor_name = [];
        try {
            foreach($res['ent.physicalMfgName']->fetchAll() as $oid) {
                $id = Helper::getIndexByOid($oid->getOid());
                if(isset($sfp_ids[$id]) && in_array($id, $mapping_id) && $oid->getValue() !== '') {
                    $vendor_name[$id] = $oid->getValue();
                }
            }
        } catch (\Exception $e) {}

        $part_number = [];
        try {
            foreach($res['ent.physicalModelName']->fetchAll() as $oid) {
                $id = Helper::getIndexByOid($oid->getOid());
                if(isset($sfp_ids[$id]) && in_array($id, $mapping_id) && $oid->getValue() !== '') {
                    $part_number[$id] = $oid->getValue();
                }
            }
        } catch (\Exception $e) {}

        $resp = [];
        foreach($mapping_id as $iface_id => $id) {
            $resp[$iface_id]['interface'] = $interfaces[$iface_id];
            $resp[$iface_id]['vendor_name'] = isset($vendor_name[$id]) ? $vendor_name[$id] : null;
            $resp[$iface_id]['part_number'] = isset($part_number[$id]) ? $part_number[$id] : null;
            $resp[$iface_id]['serial_num'] = isset($sn[$id]) ? $sn[$id] : null;
            $resp[$iface_id]['eth_compliance_codes'] = null;
            $resp[$iface_id]['baud_rate'] = null;
            $resp[$iface_id]['connector_type'] = null;
            $resp[$iface_id]['fiber_type'] = null;
            if(!isset($resp[$iface_id]['vendor_name']) && !isset($resp[$iface_id]['part_number']) && !isset($resp[$iface_id]['serial_num'])) {
                unset($resp[$iface_id]);
            }
        }
        $this->response = array_values($resp);
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }
}