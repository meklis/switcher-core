<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends ModuleAbstract {
    public function run($params = []) {
        $interfaces = [];
        $filter_iface = false;
        if($params['interface']) { 
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['_xid']] = $ifc;
            $filter_iface = true;
        } else {
            $interfaces = $this->listInterfacesByXidNames()['xid'];
        }

        $oids = [
            Oid::init($this->oids->getOidByName('zte.opticalIfacesOnPort')->getOid()),
            Oid::init($this->oids->getOidByName('zx.anOpticalVendorName')->getOid()),
            Oid::init($this->oids->getOidByName('zx.anOpticalVendorPn')->getOid()),
            Oid::init($this->oids->getOidByName('zx.anOpticalVendorSn')->getOid()),
            Oid::init($this->oids->getOidByName('zte.opticalPortType')->getOid()),
            Oid::init($this->oids->getOidByName('zx.anOpticalFiberInterfaceType')->getOid()),
            Oid::init($this->oids->getOidByName('zx.anOpticalFiberType')->getOid()),
        ];
        $res = $this->formatResponse($this->snmp->walk($oids));
        $gpon_xg_pon = [];
        foreach($res['zte.opticalIfacesOnPort']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid(), 1);
            $order = Helper::getIndexByOid($val->getOid());
            $gpon_xg_pon[$iface_xid][$order] = $val->getParsedValue();
        }
        $vendor_names = [];
        foreach($res['zx.anOpticalVendorName']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid());
            $vendor_names[$iface_xid] = $val->getParsedValue();
        }
        $part_numbers = [];
        foreach($res['zx.anOpticalVendorPn']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid());
            $part_numbers[$iface_xid] = $val->getParsedValue();
        }
        $serial_numbers = [];
        foreach($res['zx.anOpticalVendorSn']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid());
            $serial_numbers[$iface_xid] = $val->getParsedValue();
        }
        $eth_compliance_codes = [];
        foreach($res['zte.opticalPortType']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid(), 1);
            $eth_compliance_codes[$iface_xid] = $val->getParsedValue();
        }
        $connector_types = [];
        foreach($res['zx.anOpticalFiberInterfaceType']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid());
            $connector_types[$iface_xid] = $val->getParsedValue();
        }
        $fiber_types = [];
        foreach($res['zx.anOpticalFiberType']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid());
            $fiber_types[$iface_xid] = $val->getParsedValue();
        }

        $resp = [];
        foreach($interfaces as $xid => $val) {
            $resp[$xid]['interface'] = $val;
            $resp[$xid]['_double_channel'] = isset($gpon_xg_pon[$xid]) ? true : false;
            $resp[$xid]['vendor_name'] = isset($vendor_names[$xid]) ? $vendor_names[$xid] : null;
            $resp[$xid]['part_number'] = isset($part_numbers[$xid]) ? $part_numbers[$xid] : null;
            $resp[$xid]['serial_num'] = isset($serial_numbers[$xid]) ? $serial_numbers[$xid] : null;
            $resp[$xid]['eth_compliance_codes'] = isset($eth_compliance_codes[$xid]) ? $eth_compliance_codes[$xid] : null;
            $resp[$xid]['baud_rate'] = null;
            $resp[$xid]['connector_type'] = isset($connector_types[$xid]) ? $connector_types[$xid] : null;
            $resp[$xid]['_fiber_type'] = isset($fiber_types[$xid]) ? $fiber_types[$xid] : null;
            if(!isset($resp[$xid]['vendor_name']) && !isset($resp[$xid]['part_number']) && !isset($resp[$xid]['serial_num']) && !isset($resp[$xid]['eth_compliance_codes'])
            && !isset($resp[$xid]['baud_rate']) && !isset($resp[$xid]['connector_type']) && !isset($resp[$xid]['_fiber_type'])) {
                if($filter_iface) throw new \Exception('Nothing found by requested interface');
                unset($resp[$xid]);
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