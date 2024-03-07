<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends HuaweiOLTAbstractModule
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
        try {
            $data = $this->getResponseByName('ont.gpon.vendor.equipmentId');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['model'] = $this->convertHexToString($r->getHexValue());
                }
            }
        } catch (\Exception $e) {}

        try {
            $data = $this->getResponseByName('ont.epon.vendor.hardwareVer');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['ver_hardware'] = $this->convertHexToString($r->getHexValue());
                }
            }
            $data = $this->getResponseByName('ont.epon.vendor.softwareVer');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['ver_software'] = $this->convertHexToString($r->getHexValue());
                }
            }
            $data = $this->getResponseByName('ont.epon.vendor.model');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['model'] = $this->convertHexToString($r->getHexValue());
                }
            }
            $data = $this->getResponseByName('ont.epon.vendor.customInfo');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['vendor'] = $this->convertHexToString($r->getHexValue());
                }
            }
        } catch (\Exception $e) {}
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
        $requests = [];
        if($this->isHasGponIfaces()) $requests[] = $this->oids->getOidByName('ont.gpon.vendor.equipmentId');

        if($this->isHasEponIfaces()) $requests[] = $this->oids->getOidByName('ont.epon.vendor.hardwareVer');
        if($this->isHasEponIfaces()) $requests[] = $this->oids->getOidByName('ont.epon.vendor.softwareVer');
        if($this->isHasEponIfaces()) $requests[] = $this->oids->getOidByName('ont.epon.vendor.model');
        if($this->isHasEponIfaces()) $requests[] = $this->oids->getOidByName('ont.epon.vendor.customInfo');

        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $requests = array_filter($requests, function ($oid) use ($iface) {
                return strpos($oid->getName(), $iface['_technology']) !== false;
            });
            $oids = array_map(function ($e) use ($iface) {
                return  Oid::init($e->getOid() . "." . $iface['xid']);
            }, $requests);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e->getOid()); }, $requests);
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
        return trim($str, ".");
    }

}

