<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsOptical extends \SwitcherCore\Modules\VsolOlts\GPONV1600\VsolOltsAbstractModule
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
            $data = $this->getResponseByName('pon.port.optical.temp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    if($r->getValue() == 'N/A') continue;
                    $xid = "." .Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = (float)trim(str_replace(".C", "", $r->getValue()));
                }
            }
        } catch (\Exception $e) {

        }
        try {
            $data = $this->getResponseByName('pon.port.optical.voltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    if($r->getValue() == 'N/A') continue;
                    $xid = "." .Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['voltage'] = (float)trim(str_replace("V", "", $r->getValue()));
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('pon.port.optical.txPower');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    if($r->getValue() == 'N/A') continue;
                    $xid = "." .Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx'] = (float)trim(str_replace("dbm", "", $r->getValue()));
                }
            }
        } catch (\Exception $e) {
        }

        return array_values(array_map(function ($e) {
            if (!isset($e['bias'])) $e['bias'] = null;
            if (!isset($e['voltage'])) $e['voltage'] = null;
            if (!isset($e['temp'])) $e['temp'] = null;
            if (!isset($e['tx'])) $e['tx'] = null;
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
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.temp');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.voltage');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.txPower');
        }
        $oids = [];
        foreach ($info as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if($iface['type'] != 'PON') {
                throw new \Exception("Allow only for PON ports");
            }
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
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

