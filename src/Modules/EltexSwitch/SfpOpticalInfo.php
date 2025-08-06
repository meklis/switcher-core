<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\EltexSwitch\InterfacesTrait;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractInterfaces {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);
        
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        if($params['interface']) {
            $ifc = $this->parseInterface($params['interface']);
            $check_ifaces[$ifc['id']] = $ifc;
        } else {
            $check_ifaces = $this->getInterfacesIds();
        }

        $oids = [];
        foreach($check_ifaces as $snmp_id => $ifc) {
            if(!$load_only || in_array('temp', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.5");
            if(!$load_only || in_array('vcc', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.6");
            if(!$load_only || in_array('tx_bias', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.7");
            if(!$load_only || in_array('tx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.8");
            if(!$load_only || in_array('rx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.9");
            if(!$load_only || in_array('wavelength', $load_only, true) || in_array('wave_length', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.eltPhdTransceiverInfoWaveLength')->getOid() . ".{$snmp_id}");
        }
        if(!$oids) {
            $this->response = [];
            return $this;
        }
        $res = $this->formatResponse($this->snmp->get($oids));

        $response = [];
        if(isset($res['sfp.ddmValues'])) {
            foreach($res['sfp.ddmValues']->fetchAll() as $resp) {
                $iface = $check_ifaces[Helper::getIndexByOid($resp->getOid(), 1)];
                $type = null;
                $value = $resp->getValue();
                switch(Helper::getIndexByOid($resp->getOid())) {
                    case 5: $type = 'temp'; break;
                    case 6: $type = 'vcc'; $value = round($value / 1000000, 2); break;
                    case 7: $type = 'tx_bias'; $value = round($value / 1000, 2); break;
                    case 8: $type = 'tx_power'; $value = round($value / 1000, 2); break;
                    case 9: $type = 'rx_power'; $value = round($value / 1000, 2); break;
                    default: continue 2;
                }
                if(!isset($response[$iface['id']])) $response[$iface['id']]['interface'] = $iface;
                $response[$iface['id']][$type] = $value;
            }
        }
        if(isset($res['sfp.eltPhdTransceiverInfoWaveLength'])) {
            foreach($res['sfp.eltPhdTransceiverInfoWaveLength']->fetchAll() as $resp) {
                $iface = $check_ifaces[Helper::getIndexByOid($resp->getOid())];
                if(!isset($response[$iface['id']])) $response[$iface['id']]['interface'] = $iface;
                $response[$iface['id']]['_wavelength'] = $resp->getValue();
            }
        }

        foreach($response as $id => $v_arr) {
            if(!isset($v_arr['temp'])) $response[$id]['temp'] = null;
            if(!isset($v_arr['tx_bias'])) $response[$id]['tx_bias'] = null;
            if(!isset($v_arr['tx_power'])) $response[$id]['tx_power'] = null;
            if(!isset($v_arr['rx_power'])) $response[$id]['rx_power'] = null;
            if(!isset($v_arr['vcc'])) $response[$id]['vcc'] = null;
            if(!isset($v_arr['_wavelength'])) $response[$id]['_wavelength'] = null;
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