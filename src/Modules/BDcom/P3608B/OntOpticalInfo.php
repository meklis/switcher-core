<?php


namespace SwitcherCore\Modules\BDcom\P3608B;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\BDcom\BDcomAbstractModule;
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
        try {
            $data = $this->getResponseByName('ont.opticalRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['rx'] = round($r->getValue() / 10, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.opticalTx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx'] = round($r->getValue() / 10, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.opticalTemp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = round($r->getValue() / 256, 2);
                }
            }
        } catch (\Exception $e) {

        }
        try {
            $data = $this->getResponseByName('ont.opticalVoltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['voltage'] = round($r->getValue() / 10000, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if ((int)$r->getValue() == 0) continue;
                    $ifaces[$xid]['distance'] = (int)$r->getValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('pon.opticalOltRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if ($r->getValue() < -1000) continue;
                    $ifaces[$xid]['olt_rx'] = round($r->getValue() / 10, 2);
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
        if ($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("rx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalRx');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalTx');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalVoltage');
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.opticalTemp');
        }
        if (!$loadOnly || in_array("distance", $loadOnly)) {
            $info[] = $this->oids->getOidByName('ont.distance');
        }
        if ($filter['interface'] && (!$loadOnly || in_array("olt_rx", $loadOnly))) {
            $info[] = $this->oids->getOidByName('pon.opticalOltRx');
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
            $this->response = $this->formatResponse($this->snmp->get($oids, 7, 3));
        } else {
            //Load only active onts
            $responses = [];
            foreach ($this->getModule('pon_onts_status')->run(['interface'=> null, 'load_only'=>'status'])->getPrettyFiltered() as $status) {
                if($status['status'] !== 'Online') continue;
                $reqOids = array_map(function ($oid) use ($status) {
                    return Oid::init("{$oid}.{$status['interface']['xid']}");
                }, $oids);
                $resp = $this->snmp->get($reqOids, 7, 2);
                foreach ($resp as $r) {
                   $responses[] = $r;
                }
            }
            $this->response = $this->formatResponse($responses);
        }
        return $this;
    }
}

