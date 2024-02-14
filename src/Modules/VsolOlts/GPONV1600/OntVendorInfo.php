<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
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
        $data = $this->getResponseByName('ont.vendor');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['vendor'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.model');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['model'] = $r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.softwareVer');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_software'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.hardwareVer');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = "." . Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_hardware'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.onuId');
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
        $vendorInfo = [
            $this->oids->getOidByName('ont.model'),
            $this->oids->getOidByName('ont.vendor'),
            $this->oids->getOidByName('ont.onuId'),
            $this->oids->getOidByName('ont.hardwareVer'),
            $this->oids->getOidByName('ont.softwareVer'),
        ];
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

