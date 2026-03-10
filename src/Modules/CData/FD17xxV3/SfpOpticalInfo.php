<?php

namespace SwitcherCore\Modules\CData\FD17xxV3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends CDataAbstractModuleFD17xxV3 {

    public function run($params = []) {
        Helper::prepareFilter($params);
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        $interfaces = [];
        $filter_snmp_id = false;
        if($params['interface']) { 
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['_snmp_id']] = $ifc;
            $filter_snmp_id = ".{$ifc['_snmp_id']}";
        } else {
            $ifctemp = $this->getPhysicalInterfaces();
            foreach($ifctemp as $val) {
                $interfaces[$val['_snmp_id']] = $val;
            }
        }

        $oids = [];
        if(!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('ddm.gponPortTemperature')->getOid() . ($filter_snmp_id ? $filter_snmp_id : ''));
        if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('ddm.gponPortVoltage')->getOid() . ($filter_snmp_id ? $filter_snmp_id : ''));
        if(!$load_only || in_array('tx_bias', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('ddm.gponPortTxBias')->getOid() . ($filter_snmp_id ? $filter_snmp_id : ''));
        if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('ddm.gponPortTxPower')->getOid() . ($filter_snmp_id ? $filter_snmp_id : ''));
        if(!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('ddm.gponPortRxPower')->getOid() . ($filter_snmp_id ? $filter_snmp_id : ''));

        $res = $this->formatResponse($this->snmp->walk($oids));

        $temperature = [];
        if(!$load_only || in_array('temp', $load_only)) {
            foreach($res['ddm.gponPortTemperature']->fetchAll() as $val) {
                $snmp_id = Helper::getIndexByOid($val->getOid());
                $temperature[$snmp_id] = $val->getParsedValue();
            }
        }
        $vcc = [];
        if(!$load_only || in_array('vcc', $load_only)) {
            foreach($res['ddm.gponPortVoltage']->fetchAll() as $val) {
                $snmp_id = Helper::getIndexByOid($val->getOid());
                $vcc[$snmp_id] = $val->getParsedValue();
            }
        }
        $tx_bias = [];
        if(!$load_only || in_array('tx_bias', $load_only)) {
            foreach($res['ddm.gponPortTxBias']->fetchAll() as $val) {
                $snmp_id = Helper::getIndexByOid($val->getOid());
                $tx_bias[$snmp_id] = $val->getParsedValue();
                if(preg_match('/^(\-?\d+\.?\d*)\s/', $tx_bias[$snmp_id], $m)) {
                    $tx_bias[$snmp_id] = $m[1];
                }
            }
        }
        $tx_power = [];
        if(!$load_only || in_array('tx_power', $load_only)) {
            foreach($res['ddm.gponPortTxPower']->fetchAll() as $val) {
                $snmp_id = Helper::getIndexByOid($val->getOid());
                $tx_power[$snmp_id] = $val->getParsedValue();
                if(preg_match('/^(\-?\d+\.?\d*)\s/', $tx_power[$snmp_id], $m)) {
                    $tx_power[$snmp_id] = $m[1];
                }
            }
        }   
        $rx_power = [];
        if(!$load_only || in_array('rx_power', $load_only)) {
            foreach($res['ddm.gponPortRxPower']->fetchAll() as $val) {
                $snmp_id = Helper::getIndexByOid($val->getOid());
                $rx_power[$snmp_id] = $val->getParsedValue();
                if(preg_match('/^(\-?\d+\.?\d*)\s/', $rx_power[$snmp_id], $m)) {
                    $rx_power[$snmp_id] = $m[1];
                }
            }
        }
        $resp = [];
        foreach($interfaces as $snmp_id => $iface) {
            $resp[$snmp_id]['interface'] = $iface;
            $resp[$snmp_id]['vcc'] = (isset($vcc[$snmp_id]) && $vcc[$snmp_id] !== '-') ? $vcc[$snmp_id] : null;
            $resp[$snmp_id]['temp'] = (isset($temperature[$snmp_id]) && $temperature[$snmp_id] !== '-') ? $temperature[$snmp_id] : null;
            $resp[$snmp_id]['tx_power'] = (isset($tx_power[$snmp_id]) && $tx_power[$snmp_id] !== '-') ? $tx_power[$snmp_id] : null;
            $resp[$snmp_id]['rx_power'] = (isset($rx_power[$snmp_id]) && $rx_power[$snmp_id] !== '-') ? $rx_power[$snmp_id] : null;
            $resp[$snmp_id]['tx_bias'] = (isset($tx_bias[$snmp_id]) && $tx_bias[$snmp_id] !== '-') ? $tx_bias[$snmp_id] : null;
        }

        $this->response = array_values($resp);
        return $this;
    }

    public function getPretty() {
      return $this->response;
    }

    public function getPrettyFiltered($filter = [], $from_cache = false) {
        return $this->response;
    }
}
