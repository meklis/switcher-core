<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends BDcomAbstractModule
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
        foreach ($this->getInterfacesIds() as $v) {
            if(!$v['id']) continue;
            $ifaces[$v['xid']] = [
                'interface' => $v,
                'rx' => null,
                'tx' => null,
                'voltage' => null,
                'temp' => null,
                'distance' => null,
            ];
        }
        try {
            $data = $this->getResponseByName('ont.opticalRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if (!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['rx'] = round($r->getValue() / 10, 2);
                }
            }
        } catch (\Exception $e) {}
        try {
            $data = $this->getResponseByName('ont.opticalTx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if (!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['tx'] = round($r->getValue() / 10, 2);
                }
            }
        } catch (\Exception $e) {}
        $data = $this->getResponseByName('ont.opticalTemp');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['temp'] = round($r->getValue() / 256, 2);
            }
        }
        try {
        $data = $this->getResponseByName('ont.opticalVoltage');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['voltage'] = round($r->getValue() / 10000, 2);
            }
        }
        } catch (\Exception $e) {}
        try {
            $data = $this->getResponseByName('ont.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if (!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['distance'] = (int)$r->getValue();
                }
            }
        } catch (\Exception $e) {}
        return $ifaces;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $info = [];
        if(!$filter['load_only'] || strpos($filter['load_only'], "rx") !== false) {
            $info[] = $this->oids->getOidByName('ont.opticalRx');
        }
        if(!$filter['load_only'] || strpos($filter['load_only'], "tx") !== false) {
            $info[] = $this->oids->getOidByName('ont.opticalTx');
        }
        if(!$filter['load_only'] || strpos($filter['load_only'], "voltage") !== false) {
            $info[] = $this->oids->getOidByName('ont.opticalVoltage');
        }
        if(!$filter['load_only'] || strpos($filter['load_only'], "temp") !== false) {
            $info[] = $this->oids->getOidByName('ont.opticalTemp');
        }
        if(!$filter['load_only'] || strpos($filter['load_only'], "distance") !== false) {
            $info[] = $this->oids->getOidByName('ont.distance');
        }
        $oids = [];
        foreach ($info as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
        }
        $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
}

