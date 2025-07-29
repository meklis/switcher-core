<?php

namespace SwitcherCore\Modules\Raisecom;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractInterfaces {
    use InterfacesTrait;

    public function run($params = []) {
        Helper::prepareFilter($params);
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        $filter_iface = false;
        if($params['interface']) {
            $ifc = $this->parseInterface($params['interface']);
            $filter_iface = $ifc['id'];
            $check_ifaces[$ifc['_snmp_id']] = $ifc;
        } else {
            $check_ifaces = $this->getInterfacesWithConnectorTypeInfo();
        }
        $oids = [];
        foreach($check_ifaces as $snmp_id => $ifc) {
            if(!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.1");
            if(!$load_only || in_array('tx_bias', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.2");
            if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.3");
            if(!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.4");
            if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid() . ".{$snmp_id}.5");
        }
        if(!$oids) {
            $this->response = [];
            return $this;
        }
        $res = $this->formatResponse($this->snmp->get($oids));

        $response = [];
        foreach($res['sfp.ddmValues']->fetchAll() as $resp) {
            $iface = $check_ifaces[Helper::getIndexByOid($resp->getOid(), 1)];
            $type = null;
            switch(Helper::getIndexByOid($resp->getOid())) {
                case 1: $type = 'temp'; break;
                case 2: $type = 'tx_bias'; break;
                case 3: $type = 'tx_power'; break;
                case 4: $type = 'rx_power'; break;
                case 5: $type = 'vcc'; break;
                default: continue 2;
            }
            if(!isset($response[$iface['id']])) $response[$iface['id']]['interface'] = $iface;
            $response[$iface['id']][$type] = ($resp->getValue() != 0) ? round($resp->getValue() / 1000, 2) : null;
        }


        foreach($response as $id => $v_arr) {
            if(!isset($v_arr['temp']) && !isset($v_arr['tx_bias']) && !isset($v_arr['tx_power']) && !isset($v_arr['rx_power']) && !isset($v_arr['vcc'])) {
                unset($response[$id]);
            } else {
                if(!isset($v_arr['temp'])) $response[$id]['temp'] = null;
                if(!isset($v_arr['tx_bias'])) $response[$id]['tx_bias'] = null;
                if(!isset($v_arr['tx_power'])) $response[$id]['tx_power'] = null;
                if(!isset($v_arr['rx_power'])) $response[$id]['rx_power'] = null;
                if(!isset($v_arr['vcc'])) $response[$id]['vcc'] = null;
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
