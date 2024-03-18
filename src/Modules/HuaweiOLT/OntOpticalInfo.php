<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends HuaweiOLTAbstractModule
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
        $ifaces = [];
        try {
            $data = $this->getResponseByName('ont.gpon.opticalRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['rx'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.gpon.opticalTx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.gpon.opticalTemp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = round($r->getValue() , 2);
                }
            }
        } catch (\Exception $e){}
        try {
            $data = $this->getResponseByName('ont.gpon.opticalVoltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['voltage'] = round($r->getValue() / 1000, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.gpon.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if((int)$r->getValue() == 0) continue;
                    $ifaces[$xid]['distance'] = (int)$r->getValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.gpon.opticalOltRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if($r->getValue() < -1000) continue;
                    $ifaces[$xid]['olt_rx'] =  round(($r->getValue() / 100) - 100, 2);
                }
            }
        } catch (\Exception $e) {
        }


        try {
            $data = $this->getResponseByName('ont.epon.opticalRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['rx'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.epon.opticalTx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['tx'] = round($r->getValue() / 100, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.epon.opticalTemp');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['temp'] = round($r->getValue() , 2);
                }
            }
        } catch (\Exception $e){}
        try {
            $data = $this->getResponseByName('ont.epon.opticalVoltage');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['voltage'] = round($r->getValue() / 1000, 2);
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.epon.distance');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if((int)$r->getValue() == 0) continue;
                    $ifaces[$xid]['distance'] = (int)$r->getValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.epon.opticalOltRx');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid(), 1) .".". Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    if($r->getValue() < -1000) continue;
                    $ifaces[$xid]['olt_rx'] =  round(($r->getValue() / 100) - 100, 2);
                }
            }
        } catch (\Exception $e) {
        }


        return array_values(array_map(function ($e) {
            if (!isset($e['distance']) || $e['distance'] == -1) $e['distance'] = null;
            if (!isset($e['voltage'])  || $e['voltage'] > 100) $e['voltage'] = null;
            if (!isset($e['temp'])  || $e['temp'] > 100) $e['temp'] = null;
            if (!isset($e['rx'])  || $e['rx'] > 100) $e['rx'] = null;
            if (!isset($e['tx']) || $e['tx'] > 100) $e['tx'] = null;
            if (!isset($e['olt_rx']) || $e['olt_rx'] > 100) $e['olt_rx'] = null;

            if($e['rx'] <= -50) $e['rx'] = null;
            if($e['olt_rx'] <= -50) $e['olt_rx'] = null;
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
        $oidToRequest = [];
        $loadOnly = [];
        if($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("rx", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.opticalRx');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.opticalRx');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.opticalTx');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.opticalTx');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.opticalVoltage');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.opticalVoltage');
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.opticalTemp');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.opticalTemp');
        }
        if (!$loadOnly || in_array("distance", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.distance');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.distance');
        }
        if (!$loadOnly || in_array("olt_rx", $loadOnly)) {
            if($this->isHasGponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.gpon.opticalOltRx');
            if($this->isHasEponIfaces()) $oidToRequest[] = $this->oids->getOidByName('ont.epon.opticalOltRx');
        }
        if ($filter['interface']) {
            $choosedIface = $this->parseInterface($filter['interface']);
            if($choosedIface['type'] == 'ONU') {
                $oidsFiltered = array_filter($oidToRequest, function ($oid) use ($choosedIface) {
                    return strpos($oid->getName(), $choosedIface['_technology']) !== false;
                });
                $oids = array_map(function ($e) use ($choosedIface) {
                    return  Oid::init($e->getOid() . "." . $choosedIface['xid']);
                }, $oidsFiltered);
                $this->response = $this->formatResponse($this->snmp->get($oids));
            } else {
                $reqOids = [];
                $ifaces = $this->getModule('pon_onts_status')->run(['interface' =>  null, 'load_only'=>'status'])->getPrettyFiltered(['interface'=>null, 'load_only'=>'status']);
                foreach ($ifaces as $ifaceStatus) {
                    if($ifaceStatus['interface']['parent'] != $choosedIface['id']) continue;
                    if($ifaceStatus['status'] !== 'Online') continue;

                    $iface = $ifaceStatus['interface'];
                    $oidsFiltered = array_filter($oidToRequest, function ($oid) use ($iface) {
                        return strpos($oid->getName(), $iface['_technology']) !== false;
                    });
                    $oids = array_map(function ($e) use ($iface) {
                        return  Oid::init($e->getOid() . "." . $iface['xid']);
                    }, $oidsFiltered);
                    $reqOids = array_merge($reqOids, $oids);
                }
                $this->response = $this->formatResponse($this->snmp->get($reqOids));
            }
        } else {
            $ifaces = $this->getModule('pon_onts_status')->run(['interface' =>  null, 'load_only'=>'status'])->getPrettyFiltered(['interface'=>null, 'load_only'=>'status']);
            $reqOids = [];
            foreach ($ifaces as $iface) {
                if($iface['status'] !== 'Online') continue;
                $oidsFiltered = array_filter($oidToRequest, function ($oid) use ($iface) {
                    return strpos($oid->getName(), $iface['_technology']) !== false;
                });
                $oids = array_map(function ($e) use ($iface) {
                    return  Oid::init($e->getOid() . "." . $iface['xid']);
                }, $oidsFiltered);
                $reqOids = array_merge($reqOids, $oids);
            }
            $this->response = $this->formatResponse($this->snmp->get($reqOids));
        }
        return $this;
    }
}

