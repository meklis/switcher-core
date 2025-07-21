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
        $errors = [];
        foreach ($response as $name => $resp) {
            $metricName = str_replace(['sfp_media_'], '', Helper::fromCamelCase($name));
            if($resp->error())  {
                $errors[] = $resp->error();
                continue;
            }
            foreach ($resp->fetchAll() as $value) {
                $iface = $this->parseInterface(Helper::getIndexByOid($value->getOid()));
                $RESPONSES[$iface['id']]['interface'] = $iface;
                $RESPONSES[$iface['id']][$metricName] = $value->getParsedValue();
            }
        }
        // Удалим пустые ответы
        foreach ($RESPONSES as $id => $RESPONS) {
            unset($RESPONS['interface']);

            if(isset($RESPONS['fiber_type']) && $RESPONS['fiber_type'] == "Cooper") {
                unset($RESPONSES[$id]);
                continue;
            }
            if(count(array_filter($RESPONS, function ($e) {
                  return $e !== null;
            })) === 0) {
                unset($RESPONSES[$id]);
            }
        }
        if(count($RESPONSES) === 0) {
            throw new \Exception("Nothing to show. Errors - " . json_encode($errors));
        }

        //Заполним нулами обязательные, но пустые значения
        $this->response = array_values(array_map(function ($e) {
            if(!isset($e['serial_num'])) $e['serial_num'] = null;
            if(!isset($e['vendor_name'])) $e['vendor_name'] = null;
            if(!isset($e['connector_type'])) $e['connector_type'] = null;
            if(!isset($e['part_number'])) $e['part_number'] = null;
            if(!isset($e['fiber_type'])) $e['fiber_type'] = null;
            return $e;
        },$RESPONSES));
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