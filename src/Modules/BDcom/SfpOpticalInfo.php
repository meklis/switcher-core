<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SfpOpticalInfo extends BDcomAbstractModule
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
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['temp'] = round($r->getValue() / 256, 2);
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('pon.port.optical.voltage');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['vcc'] = round($r->getValue() / 10000, 2);
                }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('pon.port.optical.bias');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx_bias'] = (int)$r->getValue();
                }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('pon.port.optical.txPower');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx_power'] = round($r->getValue() / 10, 2);
                }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('pon.port.optical.txPower');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx_power'] = round($r->getValue() / 10, 2);
                }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('sfp.ddm.txPower');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx_power'] = round($r->getValue() / 10, 2);
                }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('sfp.ddm.rxPower');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['rx_power'] = round($r->getValue() / 10, 2);
                }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('sfp.ddm.temp');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = round($r->getValue() / 256, 2);
                }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('sfp.ddm.voltage');
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['vcc'] = round($r->getValue() / 10000, 2);
                }
        } catch (\Exception $e) {
        }

        return array_values(array_map(function ($e) {
            if (!isset($e['tx_bias'])) $e['tx_bias'] = null;
            if (!isset($e['vcc'])) $e['vcc'] = null;
            if (!isset($e['temp'])) $e['temp'] = null;
            if (!isset($e['tx_power'])) $e['tx_power'] = null;
            if (!isset($e['rx_power'])) $e['rx_power'] = null;
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
        if ($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.temp');
            try {
                $info[] = $this->oids->getOidByName('sfp.ddm.temp');
            } catch (\Exception $e) {
            }
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.voltage');
            try {
                $info[] = $this->oids->getOidByName('sfp.ddm.voltage');
            } catch (\Exception $e) {
            }
        }
        if (!$loadOnly || in_array("bias", $loadOnly)) {
            try {
                $info[] = $this->oids->getOidByName('pon.port.optical.bias');
            } catch (\Exception $e) {
            }
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            $info[] = $this->oids->getOidByName('pon.port.optical.txPower');
            try {
                $info[] = $this->oids->getOidByName('sfp.ddm.txPower');
            } catch (\Exception $e) {
            }
        }
        if (!$loadOnly || in_array("rx", $loadOnly)) {
            try {
                $info[] = $this->oids->getOidByName('sfp.ddm.rxPower');
            } catch (\Exception $e) {
            }
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
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $preparedRequestedOids = [];
            foreach ($this->getPhysicalInterfaces() as $interface) {
                $preparedRequestedOids = array_merge($preparedRequestedOids, array_map(function ($e) use ($interface) {
                    return Oid::init($e . "." . $interface['xid']);
                }, $oids));
            }
            $this->response = $this->formatResponse($this->snmp->get($preparedRequestedOids));
        }
        return $this;
    }
}

