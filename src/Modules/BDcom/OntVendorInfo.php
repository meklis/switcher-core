<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
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
        foreach ($this->getInterfacesIds() as $v) {
            if(!$v['id']) continue;
            $ifaces[$v['xid']] = [
                'interface' => $v,
                'vendor' => null,
                'model' => null,
                'ver_software' => null,
                'ver_hardware' => null,
            ];
        }
        $data = $this->getResponseByName('ont.vendor');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['vendor'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.model');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['model'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.verSoftware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['ver_software'] = $this->convertBytesToVersion($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.verHardware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['ver_hardware'] = $this->convertHexToString($r->getHexValue());
            }
        }
        return array_values($ifaces);
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $vendorInfo[] = $this->oids->getOidByName('ont.verSoftware');
        $vendorInfo[] = $this->oids->getOidByName('ont.verHardware');
        $vendorInfo[] = $this->oids->getOidByName('ont.vendor');
        $vendorInfo[] = $this->oids->getOidByName('ont.model');

        $oids = [];
        foreach ($vendorInfo as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->getInterfacesIds();
            $oids = array_map(function ($e) use ($iface) {
                return $e . $iface['_llid_id'];
            }, $oids);
        }
        $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }

    private function convertBytesToVersion($hex) {
        $symbols = explode(":", $hex);
        $str = '';
        foreach ($symbols as $symbol) {
            $str .= hexdec($symbol) . ".";
        }
        return trim($str, ".");
    }

    private function convertHexToString($string) {
        $symbols = explode(":", $string);
        $str = '';
        foreach ($symbols as $symbol) {
            if(!hexdec($symbol)) continue;
            $char = Helper::hexToStr($symbol);
            if(!mb_detect_encoding($char, 'ASCII', true)) {
                continue;
            }

            $str .= $char;
        }
        return $str;
    }
}

