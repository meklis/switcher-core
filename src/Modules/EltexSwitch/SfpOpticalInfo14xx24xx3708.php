<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\EltexSwitch\InterfacesTrait;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo14xx24xx3708 extends AbstractInterfaces {
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
            if(!$load_only || in_array('temp', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.1.1");
            if(!$load_only || in_array('vcc', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.2.1");
            if(!$load_only || in_array('tx_bias', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.3.1");
            if(!$load_only || in_array('tx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.4.1");
            if(!$load_only || in_array('rx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.5.1");
            if(!$load_only || in_array('wavelength', $load_only, true) || in_array('wave_length', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('sfp.waveLength')->getOid() . ".{$snmp_id}");
        }
        if(!$oids) {
            $this->response = [];
            return $this;
        }
        $res = $this->formatResponse($this->snmp->get($oids));

        $response = [];
        if(isset($res['sfp.ddmValues'])) {
            foreach($res['sfp.ddmValues']->fetchAll() as $resp) {
                $iface = $check_ifaces[Helper::getIndexByOid($resp->getOid(), 2)];
                $type = null;
                $value = $resp->getValue();
                if($value === 'NULL' || $value == 0) continue;
                switch(Helper::getIndexByOid($resp->getOid(), 1)) {
                    case 1: $type = 'temp'; break;
                    case 2: $type = 'vcc'; break;
                    case 3: $type = 'tx_bias'; break;
                    case 4: $type = 'tx_power'; $value = round($value / 1000, 2); break;
                    case 5: $type = 'rx_power'; $value = round($value / 1000, 2); break;
                    default: continue 2;
                }
                if(!isset($response[$iface['id']])) $response[$iface['id']]['interface'] = $iface;
                $response[$iface['id']][$type] = $value;
            }
        }
        if(isset($res['sfp.waveLength'])) {
            foreach($res['sfp.waveLength']->fetchAll() as $resp) {
                $iface = $check_ifaces[Helper::getIndexByOid($resp->getOid())];
                $value = $resp->getValue();
                if($value === 'NULL' || $value == 0) continue;
                if(!isset($response[$iface['id']])) $response[$iface['id']]['interface'] = $iface;
                $response[$iface['id']]['_wavelength'] = $value;
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