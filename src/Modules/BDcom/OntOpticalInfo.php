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
        $data = $this->getResponseByName('ont.opticalRx');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['rx'] = round($r->getValue() / 10, 2);
            }
        }
        $data = $this->getResponseByName('ont.opticalTx');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['tx'] = round($r->getValue() / 10, 2);
            }
        }
        $data = $this->getResponseByName('ont.opticalTemp');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['temp'] = round($r->getValue() / 256, 2);
            }
        }
        $data = $this->getResponseByName('ont.opticalVoltage');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['voltage'] = round($r->getValue() / 10000, 2);
            }
        }
        $data = $this->getResponseByName('ont.distance');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['distance'] = (int)$r->getValue();
            }
        }
        return $ifaces;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $info = $this->oids->getOidsByRegex('ont.optical*');
        $info[] = $this->oids->getOidByName('ont.distance');
        $oids = [];
        foreach ($info as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->getInterfacesIds();
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
        }
        $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
}

