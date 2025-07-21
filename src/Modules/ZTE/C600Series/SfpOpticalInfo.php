<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends ModuleAbstract {

    public function run($params = []) {
        Helper::prepareFilter($params);
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        $interfaces = [];
        if($params['interface']) { 
            $ifc = $this->parseInterface($params['interface']);
            $interfaces[$ifc['_xid']] = $ifc;
            $filter_iface = true;
        } else {
            $interfaces = $this->listInterfacesByXidNames()['xid'];
            $filter_iface = false;
        }

        $oids[] = Oid::init($this->oids->getOidByName('zte.opticalIfacesOnPort')->getOid());
        if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalSupplyVoltage')->getOid());
        if(!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalTemperature')->getOid());
        if(!$load_only || in_array('wavelength', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalWavelength')->getOid());
        if(!$load_only || in_array('wavelength', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalChanWavelength')->getOid());
        if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalC1DTxPower')->getOid());
        if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalChanCurrTxPwr')->getOid());
        if(!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalC1DRxPower')->getOid());
        if(!$load_only || in_array('tx_bias', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalC1DBiasCurrent')->getOid());
        if(!$load_only || in_array('tx_bias', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalChanBiasCurrent')->getOid());
        if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalSupplyVoltage')->getOid());
        if(!$load_only) $oids[] = Oid::init($this->oids->getOidByName('zx.anOpticalChanRxNoise')->getOid());
        if(!$load_only) $oids[] = Oid::init($this->oids->getOidByName('zte.opticalLosState')->getOid());
        $res = $this->formatResponse($this->snmp->walk($oids));

        $gpon_xg_pon = [];
        foreach($res['zte.opticalIfacesOnPort']->fetchAll() as $val) {
            $iface_xid = Helper::getIndexByOid($val->getOid(), 1);
            $order = Helper::getIndexByOid($val->getOid());
            $gpon_xg_pon[$iface_xid][$order] = $val->getParsedValue();
        }
        $vcc = [];
        if(!$load_only || in_array('vcc', $load_only)) {
            foreach($res['zx.anOpticalSupplyVoltage']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                $vcc[$iface_xid] = $val->getParsedValue() / 1000;
                if($vcc[$iface_xid] === 2147483.647) $vcc[$iface_xid] = null;
            }
        }
        $wave_length = [];
        if(!$load_only || in_array('wavelength', $load_only)) {
            foreach($res['zx.anOpticalWavelength']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                if(isset($gpon_xg_pon[$iface_xid])) continue;
                $wave_length[$iface_xid] = $val->getParsedValue();
                if($wave_length[$iface_xid] == 2147483647) $wave_length[$iface_xid] = null;
            }
        }
        $temperature = [];
        if(!$load_only || in_array('temp', $load_only)) {
            foreach($res['zx.anOpticalTemperature']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                $temperature[$iface_xid] = $val->getParsedValue() / 1000;
                if($temperature[$iface_xid] === 2147483.647) $temperature[$iface_xid] = null;
            }
        }
        $tx_power = [];
        if(!$load_only || in_array('tx_power', $load_only)) {
            foreach($res['zx.anOpticalC1DTxPower']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                if(isset($gpon_xg_pon[$iface_xid])) continue;
                $tx_power[$iface_xid] = $val->getParsedValue() / 1000;
                if($tx_power[$iface_xid] === 2147483.647) $tx_power[$iface_xid] = null;
            }
        }
        $rx_power = [];
        if(!$load_only || in_array('rx_power', $load_only)) {
            foreach($res['zx.anOpticalC1DRxPower']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                if(isset($gpon_xg_pon[$iface_xid])) continue;
                $rx_power[$iface_xid] = $val->getParsedValue() / 1000;
                if($rx_power[$iface_xid] === 2147483.647) $rx_power[$iface_xid] = null;
            }
        }
        $tx_bias = [];
        if(!$load_only || in_array('tx_bias', $load_only)) {
            foreach($res['zx.anOpticalC1DBiasCurrent']->fetchAll() as $val) {
                $iface_xid = Helper::getIndexByOid($val->getOid());
                if(isset($gpon_xg_pon[$iface_xid])) continue;
                $tx_bias[$iface_xid] = $val->getParsedValue() / 1000;
                if($tx_bias[$iface_xid] === 2147483.647) $tx_bias[$iface_xid] = null;
            }
        }
        $chan_wave_length = [];
        if(!$load_only || in_array('wavelength', $load_only)) {
            foreach($res['zx.anOpticalChanWavelength']->fetchAll() as $val) {
                $chan_id = Helper::getIndexByOid($val->getOid());
                $chan_wave_length[$chan_id] = $val->getParsedValue() / 100;
                if($chan_wave_length[$chan_id] == 21474836.47 || $chan_wave_length[$chan_id] == 2147483647) $wave_length[$iface_xid] = null;
            }
        }
        $chan_tx_power = [];
        if(!$load_only || in_array('tx_power', $load_only)) { 
            foreach($res['zx.anOpticalChanCurrTxPwr']->fetchAll() as $val) {
                $chan_id = Helper::getIndexByOid($val->getOid());
                $chan_tx_power[$chan_id] = $val->getParsedValue() / 1000;
                if($chan_tx_power[$chan_id] === 2147483.647) $chan_tx_power[$chan_id] = null;
            }
        }
        $chan_tx_bias = [];
        if(!$load_only || in_array('tx_bias', $load_only)) { 
            foreach($res['zx.anOpticalChanBiasCurrent']->fetchAll() as $val) {
                $chan_id = Helper::getIndexByOid($val->getOid());
                $chan_tx_bias[$chan_id] = $val->getParsedValue() / 1000;
                if($chan_tx_bias[$chan_id] === 2147483.647) $chan_tx_bias[$chan_id] = null;
            }
        }
        $chan_rx_noise = [];
        if(!$load_only) {
            foreach($res['zx.anOpticalChanRxNoise']->fetchAll() as $val) {
                $chan_id = Helper::getIndexByOid($val->getOid());
                $chan_rx_noise[$chan_id] = $val->getParsedValue() / 1000;
                if($chan_rx_noise[$chan_id] === 2147483.647) $chan_rx_noise[$chan_id] = null;
            }
        }
        $chan_los_state = [];
        if(!$load_only) {
            foreach($res['zte.opticalLosState']->fetchAll() as $val) {
                $chan_id = Helper::getIndexByOid($val->getOid());
                $chan_los_state[$chan_id] = $val->getParsedValue();
            }
        }


        $resp = [];
        foreach($interfaces as $xid => $iface) {
            $resp[$xid]['interface'] = $iface;
            $resp[$xid]['vcc'] = isset($vcc[$xid]) ? $vcc[$xid] : null;
            $resp[$xid]['temp'] = isset($temperature[$xid]) ? $temperature[$xid] : null;
            if(!isset($gpon_xg_pon[$xid])) {
                $resp[$xid]['_wave_length'] = isset($wave_length[$xid]) ? $wave_length[$xid] : null;
                $resp[$xid]['tx_power'] = isset($tx_power[$xid]) ? $tx_power[$xid] : null;
                $resp[$xid]['rx_power'] = isset($rx_power[$xid]) ? $rx_power[$xid] : null;
                $resp[$xid]['tx_bias'] = isset($tx_bias[$xid]) ? $tx_bias[$xid] : null;
            } else {
                $chan_id = isset($gpon_xg_pon[$xid]['1']) ? $gpon_xg_pon[$xid]['1'] : false;
                if(!$chan_id) continue;
                $resp[$xid]['_first_channel']['_wave_length'] = isset($chan_wave_length[$chan_id]) ? $chan_wave_length[$chan_id] : null;
                $resp[$xid]['_first_channel']['tx_power'] = isset($chan_tx_power[$chan_id]) ? $chan_tx_power[$chan_id] : null;
                $resp[$xid]['_first_channel']['tx_bias'] = isset($chan_tx_bias[$chan_id]) ? $chan_tx_bias[$chan_id] : null;
                $resp[$xid]['_first_channel']['_rx_noise'] = isset($chan_rx_noise[$chan_id]) ? $chan_rx_noise[$chan_id] : null;
                $resp[$xid]['_first_channel']['_los_state'] = isset($chan_los_state[$chan_id]) ? $chan_los_state[$chan_id] : null;
                $chan_id = isset($gpon_xg_pon[$xid]['2']) ? $gpon_xg_pon[$xid]['2'] : false;
                if(!$chan_id) continue;
                $resp[$xid]['_second_channel']['_wave_length'] = isset($chan_wave_length[$chan_id]) ? $chan_wave_length[$chan_id] : null;
                $resp[$xid]['_second_channel']['tx_power'] = isset($chan_tx_power[$chan_id]) ? $chan_tx_power[$chan_id] : null;
                $resp[$xid]['_second_channel']['tx_bias'] = isset($chan_tx_bias[$chan_id]) ? $chan_tx_bias[$chan_id] : null;
                $resp[$xid]['_second_channel']['_rx_noise'] = isset($chan_rx_noise[$chan_id]) ? $chan_rx_noise[$chan_id] : null;
                $resp[$xid]['_second_channel']['_los_state'] = isset($chan_los_state[$chan_id]) ? $chan_los_state[$chan_id] : null;
            }
            if(!isset($resp[$xid]['vcc']) && !isset($resp[$xid]['temp']) && !isset($resp[$xid]['_wave_length']) && !isset($resp[$xid]['tx_power']) 
            && !isset($resp[$xid]['rx_power']) && !isset($resp[$xid]['tx_bias']) && !isset($resp[$xid]['_first_channel']['_wave_length'])
            && !isset($resp[$xid]['_first_channel']['_wave_length']) && !isset($resp[$xid]['_first_channel']['tx_power']) && !isset($resp[$xid]['_first_channel']['tx_bias'])
            && !isset($resp[$xid]['_first_channel']['_rx_noise']) && !isset($resp[$xid]['_first_channel']['_los_state']) 
            && !isset($resp[$xid]['_second_channel']['_wave_length']) && !isset($resp[$xid]['_second_channel']['tx_power']) && !isset($resp[$xid]['_second_channel']['tx_bias'])
            && !isset($resp[$xid]['_second_channel']['_rx_noise']) && !isset($resp[$xid]['_second_channel']['_los_state'])) {
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
