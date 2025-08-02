<?php

namespace SwitcherCore\Modules\HuaweiOLT;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SfpMediaInfo extends HuaweiOLTAbstractModule {

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
            $data = $this->getResponseByName('pon.vendor.name');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['vendor_name'] = trim($r->getValue()) ;
                }
            }
        } catch (\Exception $e) {}
        try {
            $data = $this->getResponseByName('pon.vendor.sn');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['serial_num'] =  trim($r->getValue()) ;
                }
            }
        } catch (\Exception $e) {}
        try {
            $data = $this->getResponseByName('pon.vendor.xponType');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['_xpon_type'] = $r->getParsedValue() ;
                }
            }
        } catch (\Exception $e) {}

        return array_values(array_map(function ($e) {
            if(!isset($e['serial_num'])) $e['serial_num'] = null;
            if(!isset($e['connector_type'])) $e['connector_type'] = null;
            if(!isset($e['eth_compliance_codes'])) $e['eth_compliance_codes'] = null;
            if(!isset($e['baud_rate'])) $e['baud_rate'] = null;
            if(!isset($e['vendor_name'])) $e['vendor_name'] = null;
            if(!isset($e['part_number'])) $e['part_number'] = null;

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
        Helper::prepareFilter($filter);
        $oids = [];
        foreach ($this->oids->getOidsByRegex('^pon.vendor\..*') as $oid) {
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