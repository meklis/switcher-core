<?php

namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

abstract class SfpMediaInfo extends AbstractInterfaces {

    public function run($params = []) {
        $suffixOid = "";
        $filter_iface = false;
        if($params['interface']) {
            $suffixOid = ".{$this->parseInterface($params['interface'])['_snmp_id']}";
            $filter_iface = true;
        }
        $oids = array_map(function ($e) use ($suffixOid) {
            return Oid::init($e->getOid() . $suffixOid);
        }, $this->oids->getOidsByRegex('^sfp\.media.*'));
        $response = $this->formatResponse($this->snmp->walk($oids));

        $RESPONSES = [];
        foreach ($response as $name => $resp) {
            $metricName = str_replace(['sfp_media_'], '', Helper::fromCamelCase($name));
            if($resp->error()) continue;
            foreach ($resp->fetchAll() as $value) {
                $iface = $this->parseInterface(Helper::getIndexByOid($value->getOid()));
                $RESPONSES[$iface['id']]['interface'] = $iface;
                $RESPONSES[$iface['id']][$metricName] = $value->getParsedValue();
            }
        }
        foreach ($RESPONSES as $id => $RESPONS) {
            if(!isset($RESPONS['serial_num'])) $RESPONSES[$id]['serial_num'] = null;
            if(!isset($RESPONS['connector_type'])) $RESPONSES[$id]['connector_type'] = null;
            if(!isset($RESPONS['eth_compliance_codes'])) $RESPONSES[$id]['eth_compliance_codes'] = null;
            if(!isset($RESPONS['baud_rate'])) $RESPONSES[$id]['baud_rate'] = null;
            if(!isset($RESPONS['vendor_name'])) $RESPONSES[$id]['vendor_name'] = null;
            if(!isset($RESPONS['part_number'])) $RESPONSES[$id]['part_number'] = null;
            if($filter_iface && !isset($RESPONS['serial_num']) && !isset($RESPONS['connector_type']) && !isset($RESPONS['eth_compliance_codes'])
            && !isset($RESPONS['baud_rate']) && !isset($RESPONS['vendor_name']) && !isset($RESPONS['part_number'])) throw new \Exception('Nothing found by requested interface');
            if(!isset($RESPONS['serial_num']) && !isset($RESPONS['connector_type']) && !isset($RESPONS['eth_compliance_codes'])
            && !isset($RESPONS['baud_rate']) && !isset($RESPONS['vendor_name']) && !isset($RESPONS['part_number'])) unset($RESPONSES[$id]);
        }
        $this->response = array_values($RESPONSES);
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