<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends ModuleAbstract
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
        if($this->isGponCardsExist()) {
            $response = array_merge($response, $this->gponOptical());
        }
        if($this->isEponCardsExist()) {
            $response = array_merge($response, $this->eponOptical());
        }

        return array_map(function ($r) {
            if(!isset($r['rx'])) $r['rx'] = null;
            if(!isset($r['olt_rx'])) $r['olt_rx'] = null;
            if(!isset($r['tx'])) $r['tx'] = null;
            if(!isset($r['temp'])) $r['temp'] = null;
            if(!isset($r['voltage'])) $r['voltage'] = null;
            if(!isset($r['distance'])) $r['distance'] = null;
            return $r;
        }, $response);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $info = [];
        $loadOnly = [];
        $type = ['epon', 'gpon'];
        if($filter['interface']) {
            $type = [$this->parseInterface($filter['interface'])['_technology']];
        }

        if($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        if (!$loadOnly || in_array("rx", $loadOnly)) {
            if(in_array('epon', $type) &&  $this->isEponCardsExist()) $info[] = $this->oids->getOidByName('epon.optical.rx');
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.rx');
        }
        if (!$loadOnly || in_array("tx", $loadOnly)) {
            if(in_array('epon', $type) && $this->isEponCardsExist()) $info[] = $this->oids->getOidByName('epon.optical.tx');
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.tx');
        }
        if (!$loadOnly || in_array("voltage", $loadOnly)) {
            if(in_array('epon', $type) && $this->isEponCardsExist()) $info[] = $this->oids->getOidByName('epon.optical.voltage');
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.voltage');
        }
        if (!$loadOnly || in_array("temp", $loadOnly)) {
            if(in_array('epon', $type) && $this->isEponCardsExist()) $info[] = $this->oids->getOidByName('epon.optical.temp');
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.temp');
        }
        if (!$loadOnly || in_array("distance", $loadOnly)) {
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.distance');
        }
        if (!$loadOnly || in_array("olt_rx", $loadOnly)) {
            if(in_array('gpon', $type) && $this->isGponCardsExist()) $info[] = $this->oids->getOidByName('gpon.optical.olt_rx');
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);

            $oids = [];
            foreach ($info as $oid) {
                $ID = "{$oid->getOid()}.{$iface['_oid_id']}";
                if(in_array($oid->getName(), ['gpon.optical.rx', 'gpon.optical.tx', 'gpon.optical.temp', 'gpon.optical.voltage'])) {
                    $ID .= ".1";
                }
                $oids[] = Oid::init($ID);
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = [];
            foreach ($info as $oid) {
                $oids[] = Oid::init($oid->getOid());
            }
            $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        }
        return $this;
    }

    function eponOptical() {
        $ifaces = [];
        try {
            $responses = $this->getResponseByName('epon.optical.temp')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['temp'] = round($resp->getParsedValue(), 3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('epon.optical.voltage')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['voltage'] = round($resp->getParsedValue(), 3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('epon.optical.tx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['tx'] =  round($resp->getParsedValue(), 3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('epon.optical.rx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['rx'] =  round($resp->getParsedValue(), 3);
            }
        } catch (\Exception $e) {}
        return array_values($ifaces);
    }

    function gponOptical() {
        $ifaces = [];
        try {
            $responses = $this->getResponseByName('gpon.optical.rx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $val = $resp->getParsedValue();
                $ifaces[$id]['rx'] = round($val == 65535 ? null : (($val > 30000) ? ($val - 65536) * 0.002 - 30 : $val * 0.002 - 30), 3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('gpon.optical.tx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $val = $resp->getParsedValue();
                $ifaces[$id]['tx'] = round($val == 65535 ? null : (($val > 30000) ? ($val - 65536) * 0.002 - 30 : $val * 0.002 - 30),3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('gpon.optical.olt_rx')->fetchAll();

            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid(), 0);
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['olt_rx'] = round($resp->getParsedValue() / 1000, 3);
                if($ifaces[$id]['olt_rx'] == -1 || $ifaces[$id]['olt_rx'] == -80) {
                    $ifaces[$id]['olt_rx'] = 0;
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
        try {
            $responses = $this->getResponseByName('gpon.optical.temp')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['temp'] = round($resp->getParsedValue() / 256,3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('gpon.optical.voltage')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['voltage'] = round($resp->getParsedValue() * 0.02,3);
            }
        } catch (\Exception $e) {}
        try {
            $responses = $this->getResponseByName('gpon.optical.distance')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid());
                if(!isset($ifaces[$id])) {
                    $ifaces[$id] = [
                        'interface' => $this->parseInterface($id),
                    ];
                }
                $ifaces[$id]['distance'] = $resp->getParsedValue();
            }
        } catch (\Exception $e) {}
        return array_values($ifaces);
    }
}

