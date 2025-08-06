<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SwitcherCore\Modules\EltexSwitch\InterfacesTrait;
use SwitcherCore\Modules\Helper;
use SnmpWrapper\Oid;


class SfpMediaInfo extends \SwitcherCore\Modules\General\Switches\SfpMediaInfo {
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
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoConnectorType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoComplianceCode')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoVendorName')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoSerialNumber')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoPartNumber')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoFiberDiameterType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoTransferDistance')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
        ];
        try {
            $res = $this->formatResponse($this->snmp->walk($oids));
        } catch(\Exception $e) {
            $res = [];
        }

        $con_type = [];
        if(isset($res['sfp.eltPhdTransceiverInfoConnectorType'])) {
            foreach($res['sfp.eltPhdTransceiverInfoConnectorType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $con_type[$iface_id] = $val->getParsedValue();
            }
        }
        $type = [];
        if(isset($res['sfp.eltPhdTransceiverInfoType'])) {
            foreach($res['sfp.eltPhdTransceiverInfoType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $type[$iface_id] = $val->getParsedValue();
            }
        }
        $eth_comp_codes = [];
        if(isset($res['sfp.eltPhdTransceiverInfoComplianceCode'])) {
            foreach($res['sfp.eltPhdTransceiverInfoComplianceCode']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $eth_comp_codes[$iface_id] = $val->getParsedValue();
            }
        }
        $vendor_name = [];
        if(isset($res['sfp.eltPhdTransceiverInfoVendorName'])) {
            foreach($res['sfp.eltPhdTransceiverInfoVendorName']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $vendor_name[$iface_id] = $val->getParsedValue();
            }
        }
        $sn = [];
        if(isset($res['sfp.eltPhdTransceiverInfoSerialNumber'])) {
            foreach($res['sfp.eltPhdTransceiverInfoSerialNumber']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $sn[$iface_id] = $val->getParsedValue();
            }
        }
        $part_num = [];
        if(isset($res['sfp.eltPhdTransceiverInfoPartNumber'])) {
            foreach($res['sfp.eltPhdTransceiverInfoPartNumber']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $part_num[$iface_id] = $val->getParsedValue();
            }
        }
        $diameter_type = [];
        if(isset($res['sfp.eltPhdTransceiverInfoFiberDiameterType'])) {
            foreach($res['sfp.eltPhdTransceiverInfoFiberDiameterType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $diameter_type[$iface_id] = $val->getParsedValue();
            }
        }
        $transfer_distance = [];
        if(isset($res['sfp.eltPhdTransceiverInfoTransferDistance'])) {
            foreach($res['sfp.eltPhdTransceiverInfoTransferDistance']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $transfer_distance[$iface_id] = $val->getParsedValue();
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
            && !isset($response[$iface_id]['eth_compliance_codes']) && !isset($response[$iface_id]['connector_type']) && !isset($response[$iface_id]['_interface_type'])) {
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