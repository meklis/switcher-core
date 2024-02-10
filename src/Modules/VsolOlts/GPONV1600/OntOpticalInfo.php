<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $ifaces = [];
        try {
            $data = $this->getResponseByName('ont.optical.rxPower');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $snmpId = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                    $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                    $ifaces[$snmpId]['rx'] = (float)$r->getParsedValue();
                }
            }
        } catch (\Exception $e) {

        }
        try {
            $data = $this->getResponseByName('ont.optical.txPower');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $snmpId = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                    $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                    $ifaces[$snmpId]['tx'] = (float)$r->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.optical.temp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    if(!trim($r->getValue())) continue;
                    $snmpId = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                    $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                    $ifaces[$snmpId]['temp'] = (float)trim(str_replace("C", "", $r->getValue()));
                }
            }
        } catch (\Exception $e){}
        try {
            $data = $this->getResponseByName('ont.optical.voltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    if(!trim($r->getValue())) continue;
                    $snmpId = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                    $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                    $ifaces[$snmpId]['voltage'] = (float)trim(str_replace("V", "", $r->getValue()));
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.optical.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $snmpId = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                    $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                    if((int)$r->getValue() == 0) continue;
                    $ifaces[$snmpId]['distance'] = (int)$r->getValue();
                }
            }
        } catch (\Exception $e) {
        }

        return array_values(array_map(function ($e) {
            if (!isset($e['distance'])) $e['distance'] = null;
            if (!isset($e['voltage'])) $e['voltage'] = null;
            if (!isset($e['temp'])) $e['temp'] = null;
            if (!isset($e['rx'])) $e['rx'] = null;
            if (!isset($e['tx'])) $e['tx'] = null;
            if (!isset($e['olt_rx'])) $e['olt_rx'] = null;
            return $e;
        }, $ifaces));
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $info = [];
        $loadOnly = [];
        if($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("rx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.optical.rxPower');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.optical.txPower');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.optical.voltage');
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.optical.temp');
        }
        if (!$loadOnly || in_array("distance", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.optical.distance');
        }

        $oids = [];
        foreach ($info as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . $iface['_snmp_id'];
            }, $oids);
            $oids = array_map(function ($e) {
                return Oid::init($e);
            }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));

        } else {
            $oids = array_map(function ($e) {
                return Oid::init($e);
            }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }
}

