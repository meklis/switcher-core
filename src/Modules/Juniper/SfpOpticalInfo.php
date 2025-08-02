<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Juniper\InterfacesTrait;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractModule {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        $interfaces = [];
        $filter_iface = false;
        if($params['interface']) { 
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['_snmp_id']] = $ifc;
            $filter_iface = $ifc['_snmp_id'];
        } else {
            $interfaces = $this->getInterfacesIds();
        }
        $oids = [];
        if(!$load_only || in_array('vcc', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('jnx.DomCurrentModuleVoltage')->getOid() . ($filter_iface ? ".{$filter_iface}" : ''));
        if(!$load_only || in_array('temp', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('jnx.DomCurrentModuleTemperature')->getOid() . ($filter_iface ? ".{$filter_iface}" : ''));
        if(!$load_only || in_array('rx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('jnx.DomCurrentLaneRxLaserPower')->getOid() . ($filter_iface ? ".{$filter_iface}" : ''));
        if(!$load_only || in_array('tx_power', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('jnx.DomCurrentLaneTxLaserOutputPower')->getOid() . ($filter_iface ? ".{$filter_iface}" : ''));
        if(!$load_only || in_array('tx_bias', $load_only, true)) $oids[] = Oid::init($this->oids->getOidByName('jnx.DomCurrentLaneTxLaserBiasCurrent')->getOid() . ($filter_iface ? ".{$filter_iface}" : ''));
        $res = $this->formatResponse($this->snmp->walk($oids));
        $vcc = [];
        if(!$load_only || in_array('vcc', $load_only, true)) {
            foreach($res['jnx.DomCurrentModuleVoltage']->fetchAll() as $oid) {
                $vcc[Helper::getIndexByOid($oid->getOid())] = floatval($oid->getValue()) / 1000;
                if($oid->getValue() == 0) $vcc[Helper::getIndexByOid($oid->getOid())] = null;
            }
        }
        $temp = [];
        if(!$load_only || in_array('temp', $load_only, true)) {
            foreach($res['jnx.DomCurrentModuleTemperature']->fetchAll() as $oid) {
                $temp[Helper::getIndexByOid($oid->getOid())] = $oid->getValue();
                if($oid->getValue() == 0) $temp[Helper::getIndexByOid($oid->getOid())] = null;
            }
        }

        $rx_power = [];
        if(!$load_only || in_array('rx_power', $load_only, true)) {
            foreach($res['jnx.DomCurrentLaneRxLaserPower']->fetchAll() as $oid) {
                $iface_id = Helper::getIndexByOid($oid->getOid(), 1);
                $rx_power[$iface_id][Helper::getIndexByOid($oid->getOid())] = floatval($oid->getValue()) / 100;
                if($oid->getValue() == 0) $rx_power[$iface_id][Helper::getIndexByOid($oid->getOid())] = null;
                if(!isset($max_channel[$iface_id])) $max_channel[$iface_id] = 0;
                if($max_channel[$iface_id] < Helper::getIndexByOid($oid->getOid())) $max_channel[$iface_id] = Helper::getIndexByOid($oid->getOid());
            }
        }
        $tx_power = [];
        if(!$load_only || in_array('tx_power', $load_only, true)) {
            foreach($res['jnx.DomCurrentLaneTxLaserOutputPower']->fetchAll() as $oid) {
                $iface_id = Helper::getIndexByOid($oid->getOid(), 1);
                $tx_power[$iface_id][Helper::getIndexByOid($oid->getOid())] = floatval($oid->getValue()) / 100;
                if($oid->getValue() == 0) $tx_power[$iface_id][Helper::getIndexByOid($oid->getOid())] = null;
                if(!isset($max_channel[$iface_id])) $max_channel[$iface_id] = 0;
                if($max_channel[$iface_id] < Helper::getIndexByOid($oid->getOid())) $max_channel[$iface_id] = Helper::getIndexByOid($oid->getOid());
            }
        }        
        $tx_bias = [];
        if(!$load_only || in_array('tx_bias', $load_only, true)) {
            foreach($res['jnx.DomCurrentLaneTxLaserBiasCurrent']->fetchAll() as $oid) {
                $iface_id = Helper::getIndexByOid($oid->getOid(), 1);
                $tx_bias[$iface_id][Helper::getIndexByOid($oid->getOid())] = floatval($oid->getValue()) / 1000;
                if($oid->getValue() == 0) $tx_bias[$iface_id][Helper::getIndexByOid($oid->getOid())] = null;
                if(!isset($max_channel[$iface_id])) $max_channel[$iface_id] = 0;
                if($max_channel[$iface_id] < Helper::getIndexByOid($oid->getOid())) $max_channel[$iface_id] = Helper::getIndexByOid($oid->getOid());
            }
        }

        $resp = [];
        foreach($interfaces as $id => $iface) {
            if(!isset($vcc[$id]) && !isset($temp[$id]) && !isset($rx_power[$id]['0'])
            && !isset($tx_power[$id]['0']) && !isset($tx_bias[$id]['0'])) {
                continue;
            }
            $resp[$id]['interface'] = $iface;
            $resp[$id]['vcc'] = isset($vcc[$id]) ? $vcc[$id] : null;
            $resp[$id]['temp'] = isset($temp[$id]) ? $temp[$id] : null;
            
            if(!isset($max_channel[$id])) $max_channel[$id] = 0;
            if($max_channel[$id] == 0) {
                $resp[$id]['rx_power'] = isset($rx_power[$id]['0']) ? $rx_power[$id]['0'] : null;
                $resp[$id]['tx_power'] = isset($tx_power[$id]['0']) ? $tx_power[$id]['0'] : null;
                $resp[$id]['tx_bias'] = isset($tx_bias[$id]['0']) ? $tx_bias[$id]['0'] : null;
            } else {
                for($i = 0; $i <= $max_channel[$id]; $i++) {
                    switch($i) {
                        case 0: $name = '_first_channel'; break;
                        case 1: $name = '_second_channel'; break;
                        case 2: $name = '_third_channel'; break;
                        case 3: $name = '_fourth_channel'; break;
                    }
                    $resp[$id][$name]['rx_power'] = isset($rx_power[$id][$i]) ? $rx_power[$id][$i] : null;
                    $resp[$id][$name]['tx_power'] = isset($tx_power[$id][$i]) ? $tx_power[$id][$i] : null;
                    $resp[$id][$name]['tx_bias'] = isset($tx_bias[$id][$i]) ? $tx_bias[$id][$i] : null;
                }
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