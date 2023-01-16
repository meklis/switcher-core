<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends GCOMAbstractModule
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
                $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                $ifaces[$iface['id']]['interface'] = $iface;
                if(!$r->getValue()) continue;
                $ifaces[$iface['id']]['vendor'] = $r->getParsedValue();
            }
        }
        $data = $this->getResponseByName('ont.model');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                $ifaces[$iface['id']]['interface'] = $iface;
                if(!$r->getValue()) continue;
                $ifaces[$iface['id']]['model'] = $r->getParsedValue();;
            }
        }
        $data = $this->getResponseByName('ont.verSoftware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                $ifaces[$iface['id']]['interface'] = $iface;
                if(!$r->getValue()) continue;
                $ifaces[$iface['id']]['ver_software'] = $r->getParsedValue();;
            }
        }
        $data = $this->getResponseByName('ont.verHardware');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                $ifaces[$iface['id']]['interface'] = $iface;
                if(!$r->getValue()) continue;
                $ifaces[$iface['id']]['ver_hardware'] = $r->getParsedValue();;
            }
        }
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
        $vendorInfo[] = $this->oids->getOidByName('ont.vendor');
        $vendorInfo[] = $this->oids->getOidByName('ont.model');
        $vendorInfo[] = $this->oids->getOidByName('ont.verSoftware');
        $vendorInfo[] = $this->oids->getOidByName('ont.verHardware');

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

}

