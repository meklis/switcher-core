<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SwitcherCore\Modules\EltexSwitch\InterfacesTrait;
use SwitcherCore\Modules\Helper;
use SnmpWrapper\Oid;


class SfpMediaInfo14xx24xx3708 extends \SwitcherCore\Modules\General\Switches\SfpMediaInfo {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);

        $filter_iface = false;
        if($params['interface']) {
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['id']] = $ifc;
            $filter_iface = $ifc['id'];
        } else {
            $interfaces = $this->getInterfacesIds();
        }

        $oids = [
            Oid::init($this->oids->getOidByName('sfp.connectorType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.interfaceType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.ethComplianceCode')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.vendorName')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.serialNumber')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.partNumber')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.fiberDiameterType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.transferDistance')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
        ];
        try {
            $res = $this->formatResponse($this->snmp->walk($oids));
        } catch(\Exception $e) {
            $res = [];
        }

        $con_type = [];
        if(isset($res['sfp.connectorType'])) {
            foreach($res['sfp.connectorType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $con_type[$iface_id] = $val->getParsedValue();
            }
        }
        $type = [];
        if(isset($res['sfp.interfaceType'])) {
            foreach($res['sfp.interfaceType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $type[$iface_id] = $val->getParsedValue();
            }
        }
        $eth_comp_codes = [];
        if(isset($res['sfp.ethComplianceCode'])) {
            foreach($res['sfp.ethComplianceCode']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $eth_comp_codes[$iface_id] = $val->getParsedValue();
                if($val->getParsedValue() === '') $eth_comp_codes[$iface_id] = null;
            }
        }
        $vendor_name = [];
        if(isset($res['sfp.vendorName'])) {
            foreach($res['sfp.vendorName']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $vendor_name[$iface_id] = $val->getParsedValue();
                if($val->getParsedValue() === '') $vendor_name[$iface_id] = null;
            }
        }
        $sn = [];
        if(isset($res['sfp.serialNumber'])) {
            foreach($res['sfp.serialNumber']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $sn[$iface_id] = $val->getParsedValue();
                if($val->getParsedValue() === '') $sn[$iface_id] = null;
            }
        }
        $part_num = [];
        if(isset($res['sfp.partNumber'])) {
            foreach($res['sfp.partNumber']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $part_num[$iface_id] = $val->getParsedValue();
                if($val->getParsedValue() === '') $part_num[$iface_id] = null;
            }
        }
        $diameter_type = [];
        if(isset($res['sfp.fiberDiameterType'])) {
            foreach($res['sfp.fiberDiameterType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $diameter_type[$iface_id] = $val->getParsedValue();
            }
        }
        $transfer_distance = [];
        if(isset($res['sfp.transferDistance'])) {
            foreach($res['sfp.transferDistance']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $transfer_distance[$iface_id] = $val->getParsedValue();
                if($val->getParsedValue() == 0) $transfer_distance[$iface_id] = null;
            }
        }
       

        $response = [];
        foreach($interfaces as $iface_id => $iface) {
            $response[$iface_id]['interface'] = $iface;
            $response[$iface_id]['vendor_name'] = isset($vendor_name[$iface_id]) ? $vendor_name[$iface_id] : null;
            $response[$iface_id]['part_number'] = isset($part_num[$iface_id]) ? $part_num[$iface_id] : null;
            $response[$iface_id]['serial_num'] = isset($sn[$iface_id]) ? $sn[$iface_id] : null;
            $response[$iface_id]['eth_compliance_codes'] = isset($eth_comp_codes[$iface_id]) ? $eth_comp_codes[$iface_id] : null;
            $response[$iface_id]['baud_rate'] = null;
            $response[$iface_id]['connector_type'] = isset($con_type[$iface_id]) ? $con_type[$iface_id] : null;
            $response[$iface_id]['fiber_type'] = null;
            $response[$iface_id]['_interface_type'] = isset($type[$iface_id]) ? $type[$iface_id] : null;
            $response[$iface_id]['_fiber_diameter_type'] = isset($diameter_type[$iface_id]) ? $diameter_type[$iface_id] : null;
            $response[$iface_id]['_transfer_distance'] = isset($transfer_distance[$iface_id]) ? $transfer_distance[$iface_id] : null;
            if(!isset($response[$iface_id]['vendor_name']) && !isset($response[$iface_id]['part_number']) && !isset($response[$iface_id]['serial_num'])
            && !isset($response[$iface_id]['eth_compliance_codes'])) {
                unset($response[$iface_id]);
            }
        }
        $this->response = array_values($response);
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }
}