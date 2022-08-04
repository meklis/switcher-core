<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getPretty()
    {
        $ifaces = [];
        $data = $this->getResponseByName('ont.sn.vendor');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['vendor'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.sn.model');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['model'] = $r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.sn.softwareVer');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_software'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.sn.hardwareVer');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_hardware'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.sn.onuId');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['onu_id'] = $this->convertHexToString($r->getHexValue());
            }
        }
        ksort($ifaces);
        return array_values(array_map(function ($e) {
            if (!isset($e['vendor'])) $e['vendor'] = null;
            if (!isset($e['model'])) $e['model'] = null;
            if (!isset($e['ver_software'])) $e['ver_software'] = null;
            if (!isset($e['ver_hardware'])) $e['ver_hardware'] = null;
            if (!isset($e['onu_id'])) $e['onu_id'] = null;
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
        $vendorInfo = $this->oids->getOidsByRegex('^ont\.sn\..*');
        $oids = [];
        foreach ($vendorInfo as $oid) {
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

    private function convertBytesToVersion($hex)
    {
        $symbols = explode(":", $hex);
        $str = '';
        foreach ($symbols as $symbol) {
            $str .= hexdec($symbol) . ".";
        }
        return trim($str, ".");
    }

}

