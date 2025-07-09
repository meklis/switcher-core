<?php

namespace SwitcherCore\Modules\Raisecom;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractInterfaces {
    use InterfacesTrait;

    public function run($params = []) {
        $filter_iface = false;
        if($params['interface']) {
            $filter_iface = $this->parseInterface($params['interface'])['_snmp_id'];
        }
        $res = $this->formatResponse($this->snmp->walk([Oid::init($this->oids->getOidByName('sfp.ddmValues')->getOid())]));
        $response = [];
        foreach($res['sfp.ddmValues']->fetchAll() as $resp) {
            $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1));
            if($filter_iface && $iface['_snmp_id'] !== $filter_iface) continue;
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
            $response[$iface['id']][$type] = round($resp->getValue() / 1000, 2);
        }
        $this->response = $response;
        return $this;
    }
    public function getPretty()
    {
      return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
