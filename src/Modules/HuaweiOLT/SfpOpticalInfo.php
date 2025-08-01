<?php


namespace SwitcherCore\Modules\HuaweiOLT;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SfpOpticalInfo extends HuaweiOLTAbstractModule {

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
            $data = $this->getResponseByName('pon.optical.temp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = (float)$r->getValue() ;
                }
            }
        } catch (\Exception $e) {

        }
        try {
            $data = $this->getResponseByName('pon.optical.voltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['vcc'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('pon.optical.bias');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['bias'] = (int)$r->getValue() / 10;
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('pon.optical.tx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx_power'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }

        return array_values(array_map(function ($e) {
            if (!isset($e['bias']) || $e['bias'] > 10000) $e['bias'] = null;
            if (!isset($e['vcc'])  || $e['vcc'] > 10000) $e['vcc'] = null;
            if (!isset($e['temp'])  || $e['temp'] > 10000) $e['temp'] = null;
            if (!isset($e['tx_power'])  || $e['tx_power'] > 10000) $e['tx_power'] = null;
            if (!isset($e['rx_power']) ) $e['rx_power'] = null;
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
        $info = [];
        $loadOnly = [];
        if($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.optical.temp');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.optical.voltage');
        }
        if (!$loadOnly || in_array("bias", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.optical.bias');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.optical.tx');
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
