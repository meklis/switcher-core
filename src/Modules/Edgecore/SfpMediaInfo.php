<?php

namespace SwitcherCore\Modules\Edgecore;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends AbstractInterfaces
{
    use InterfacesTrait;

    use InterfacesTrait;

    public function run($params = [])
    {
        $suffixOid = "";
        if($params['interface']) {
            $suffixOid = ".{$this->parseInterface($params['interface'])['_snmp_id']}";
        }
        $oids = array_map(function ($e) use ($suffixOid) {
            return Oid::init($e->getOid() . $suffixOid);
        }, $this->oids->getOidsByRegex('^sfp.media.*'));
        $response = $this->formatResponse($this->snmp->walk($oids));

        $RESPONSES = [];
        foreach ($response as $name=>$resp) {
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
