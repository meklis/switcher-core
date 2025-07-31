<?php

namespace SwitcherCore\Modules\DCN;

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

        $oids = [Oid::init($this->oids->getOidByName('sfp.transceiverSn')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""))];
        $trans_sn = $this->formatResponse($this->snmp->walk($oids));

        $oids = [
            Oid::init($this->oids->getOidByName('sfp.interfaceType')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.vendorInfo')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
            Oid::init($this->oids->getOidByName('sfp.maxBandwidth')->getOid() . (($filter_iface) ? ".{$filter_iface}" : "")),
        ];
        try {
            $res = $this->formatResponse($this->snmp->walk($oids));
        } catch(\Exception $e) {
            $res = [];
        }

        $eth_comp_codes = [];
        if(isset($res['sfp.interfaceType'])) {
            foreach($res['sfp.interfaceType']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $eth_comp_codes[$iface_id] = $val->getParsedValue();
            }
        }

        $vendor_info = [];
        if(isset($res['sfp.vendorInfo'])) {
            foreach($res['sfp.vendorInfo']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $vendor_info[$iface_id] = $val->getParsedValue();
            }
        }

        $maxBandwidth = [];
        if(isset($res['sfp.maxBandwidth'])) {
            foreach($res['sfp.maxBandwidth']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $maxBandwidth[$iface_id] = $val->getParsedValue();
            }
        }

        $response = [];
        foreach($trans_sn['sfp.transceiverSn']->fetchAll() as $transceiver_sn) {
            if($transceiver_sn->getParsedValue() === 'null' || $transceiver_sn->getParsedValue() === 'NULL') continue;
            $iface_id = Helper::getIndexByOid($transceiver_sn->getOid());
            $response[$iface_id]['interface'] = $interfaces[$iface_id];
            $response[$iface_id]['vendor_name'] = isset($vendor_info[$iface_id]) ? $vendor_info[$iface_id] : null;
            $response[$iface_id]['part_number'] = null;
            $response[$iface_id]['serial_num'] = $transceiver_sn->getParsedValue();
            $response[$iface_id]['eth_compliance_codes'] = isset($eth_comp_codes[$iface_id]) ? $eth_comp_codes[$iface_id] : null;
            $response[$iface_id]['baud_rate'] = isset($maxBandwidth[$iface_id]) ? $maxBandwidth[$iface_id] : null;
            $response[$iface_id]['connector_type'] = null;
            $response[$iface_id]['fiber_type'] = null;
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