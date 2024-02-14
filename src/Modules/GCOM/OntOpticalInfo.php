<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends GCOMAbstractModule
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
        $response = [];
        $ifaces = [];
        try {
            $data = $this->getResponseByName('ont.opticalRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['rx'] = (float)$r->getValue() ;
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.opticalTx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['tx'] = (float)$r->getValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['distance'] = (float)$r->getValue() ;
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.opticalTemp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['temp'] = (float)$r->getValue() ;
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.opticalVoltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['voltage'] = (float)$r->getValue() ;
                }
            }
        } catch (\Exception $e) {
        }
        return $this->sortResponseByInterface(array_values(array_map(function ($e) {
            if (!isset($e['distance'])) $e['distance'] = null;
            if (!isset($e['voltage'])) $e['voltage'] = null;
            if (!isset($e['temp'])) $e['temp'] = null;
            if (!isset($e['rx'])) $e['rx'] = null;
            if (!isset($e['tx'])) $e['tx'] = null;
            if (!isset($e['olt_rx'])) $e['olt_rx'] = null;
            if (!isset($e['olt_tx'])) $e['olt_tx'] = null;
            return $e;
        }, $ifaces)));

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
            $info[] = $this->oids->getOidByName('ont.opticalRx');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalTx');
        }
        if (!$loadOnly || in_array("distance", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.distance');
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalTemp');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalVoltage');
        }
        $oids = [];
        foreach ($info as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {
                return Oid::init($e);
            }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $statuses = $this->getModule('pon_onts_status')->run(['load_only' => '', 'interface' => null])->getPretty();
            $oidList = [];
            foreach ($statuses as $status) {
                if($status['status'] === 'Online') {
                    $oidList = array_merge($oidList, array_map(function ($e) use ($status) {
                        return Oid::init($e . "." . $status['interface']['xid']);
                    }, $oids));
                }
            }
            $this->response = $this->formatResponse($this->snmp->get($oidList));
        }
        return $this;
    }
}

