<?php


namespace SwitcherCore\Modules\BDcom\P3310B;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\BDcom\BDcomAbstractModule;
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
        $data = $this->getResponseByName('ont.vendor');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['vendor'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.model');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['model'] = $r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.verSoftware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_software'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.verHardware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['ver_hardware'] = $this->convertHexToString($r->getHexValue());
            }
        }
        ksort($ifaces);
        return array_values(array_map(function ($e){
            if(!isset($e['vendor'])) $e['vendor'] = null;
            if(!isset($e['model'])) $e['model'] = null;
            if(!isset($e['ver_software'])) $e['ver_software'] = null;
            if(!isset($e['ver_hardware'])) $e['ver_hardware'] = null;
            return $e;
        },$ifaces));
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
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    private function convertBytesToVersion($hex) {
        $symbols = explode(":", $hex);
        $str = '';
        foreach ($symbols as $symbol) {
            $str .= hexdec($symbol) . ".";
        }
        foreach (["%", "/", "\\", "(", ")"] as $symbol) {
            $str = str_replace($symbol, "", $str);
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
            if(in_array($char, ["%", "/", "\\", "(", ")"])) {
                continue;
            }
            $str .= $char;
        }
        return $str;
    }
}

