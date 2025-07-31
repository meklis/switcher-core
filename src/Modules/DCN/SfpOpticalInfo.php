<?php

namespace SwitcherCore\Modules\DCN;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractInterfaces {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);
        $filter_iface = false;
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        if($params['interface']) {
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['id']] = $ifc;
            $filter_iface = $ifc['id'];
        } else {
            $interfaces = $this->getInterfacesIds();
        }

        $oids = [];
        if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmDiagnosisVoltage')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        if(!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmDiagnosisTemperature')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmDiagnosisTXPower')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        if(!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmDiagnosisRXPower')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        if(!$load_only || in_array('tx_bias', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmDiagnosisBias')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        if(!$load_only || in_array('wavelength', $load_only) || in_array('wave_length', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.waveLength')->getOid() . (($filter_iface) ? ".{$filter_iface}" : ""));
        try {
            $res = $this->formatResponse($this->snmp->walk($oids));
        } catch(\Exception $e) {
            $res = [];
        }
        
        $vcc = [];
        if(!$load_only || in_array('vcc', $load_only)) {
            foreach($res['sfp.ddmDiagnosisVoltage']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $vcc[$iface_id] = $val->getParsedValue();
                if($vcc[$iface_id] === 'null' || $vcc[$iface_id] === 'NULL') $vcc[$iface_id] = null;
            }
        }

        $temp = [];
        if(!$load_only || in_array('temp', $load_only)) {
            foreach($res['sfp.ddmDiagnosisTemperature']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $temp[$iface_id] = $val->getParsedValue();
                if($temp[$iface_id] === 'null' || $temp[$iface_id] === 'NULL') $temp[$iface_id] = null;
            }
        }

        $tx_power = [];
        if(!$load_only || in_array('vcc', $load_only)) {
            foreach($res['sfp.ddmDiagnosisTXPower']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $tx_power[$iface_id] = $val->getParsedValue();
                if($tx_power[$iface_id] === 'null' || $tx_power[$iface_id] === 'NULL') $tx_power[$iface_id] = null;
            }
        }

        $rx_power = [];
        if(!$load_only || in_array('rx_power', $load_only)) {
            foreach($res['sfp.ddmDiagnosisRXPower']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $rx_power[$iface_id] = $val->getParsedValue();
                if($rx_power[$iface_id] === 'null' || $rx_power[$iface_id] === 'NULL') $rx_power[$iface_id] = null;
            }
        }

        $tx_bias = [];
        if(!$load_only || in_array('tx_bias', $load_only)) {
            foreach($res['sfp.ddmDiagnosisBias']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $tx_bias[$iface_id] = $val->getParsedValue();
                if($tx_bias[$iface_id] === 'null' || $tx_bias[$iface_id] === 'NULL') $tx_bias[$iface_id] = null;
            }
        }

        $wave_length = [];
        if(!$load_only || in_array('wavelength', $load_only) || in_array('wave_length', $load_only)) {
            foreach($res['sfp.waveLength']->fetchAll() as $val) {
                $iface_id = Helper::getIndexByOid($val->getOid());
                $wave_length[$iface_id] = $val->getParsedValue();
                if($wave_length[$iface_id] === 'null' || $wave_length[$iface_id] === 'NULL') $wave_length[$iface_id] = null;
            }
        }

        $resp = [];
        foreach($interfaces as $iface_id => $iface) {
            $resp[$iface_id]['interface'] = $iface;
            $resp[$iface_id]['vcc'] = isset($vcc[$iface_id]) ? $vcc[$iface_id] : null;
            $resp[$iface_id]['temp'] = isset($temp[$iface_id]) ? $temp[$iface_id] : null;
            $resp[$iface_id]['tx_power'] = isset($tx_power[$iface_id]) ? $tx_power[$iface_id] : null;
            $resp[$iface_id]['rx_power'] = isset($rx_power[$iface_id]) ? $rx_power[$iface_id] : null;
            $resp[$iface_id]['tx_bias'] = isset($tx_bias[$iface_id]) ? $tx_bias[$iface_id] : null;
            $resp[$iface_id]['_wave_length'] = isset($wave_length[$iface_id]) ? $wave_length[$iface_id] : null;
            if(!isset($resp[$iface_id]['vcc']) && !isset($resp[$iface_id]['temp']) && !isset($resp[$iface_id]['tx_power']) 
            && !isset($resp[$iface_id]['rx_power']) && !isset($resp[$iface_id]['tx_bias'])) {
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
