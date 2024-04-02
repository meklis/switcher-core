<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfoV1 extends ModuleAbstract
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
        $response = $this->parseOpticalResponse();

        return array_map(function ($r) {
            if (!isset($r['rx'])) $r['rx'] = null;
            if (!isset($r['olt_rx'])) $r['olt_rx'] = null;
            if (!isset($r['tx'])) $r['tx'] = null;
            if (!isset($r['temp'])) $r['temp'] = null;
            if (!isset($r['voltage'])) $r['voltage'] = null;
            if (!isset($r['distance'])) $r['distance'] = null;

            if ($r['rx'] <= -70) {
                $r['rx'] = null;
            }
            if ($r['olt_rx'] <= -70) {
                $r['olt_rx'] = null;
            }

            if ($r['rx'] >= 30) {
                $r['rx'] = null;
            }
            if ($r['olt_rx'] >= 30) {
                $r['olt_rx'] = null;
            }

            return $r;
        }, $response);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

    protected $_mustLoadedOidNames = [];

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $ifaces = $this->prepareInterfacesList($filter['interface']);

        if (count($ifaces) == 0) {
            $this->response = [];
            return $this;
        }

        $loadOnly = [];
        if ($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        } elseif (!$filter['load_only'] && !$filter['interface'] && !preg_match('/fw_1_2$/', $this->model->getKey())) {
            $loadOnly = ['rx', 'tx', 'olt_rx', 'distance'];
        } elseif (!$filter['load_only'] && !$filter['interface'] && preg_match('/fw_1_2$/', $this->model->getKey())) {
            $loadOnly = ['rx', 'tx', 'olt_rx'];
        }

        $loadingOidNames = [];
        $oids = [];
        $addOid = function ($oidName, $iface) use (&$loadingOidNames, &$oids) {
            $oid = $this->oids->getOidByName($oidName);
            $blk = '';
            if (in_array($oid->getName(), [
                'gpon.optical.rx',
                'gpon.optical.tx',
                'gpon.optical.temp',
                'gpon.optical.voltage',
            ])) {
                $blk .= ".1";
            }
            if ($oid->getName() === 'gpon.optical.olt_rx' && $iface['_technology'] === 'epon') {
                $oids[] = Oid::init($oid->getOid() . "." . $iface['_gpon_format'] . $blk);
            } else {
                $oids[] = Oid::init($oid->getOid() . "." . $iface['_oid_id'] . $blk);
            }
            $loadingOidNames[$oidName] = true;
        };

        foreach ($ifaces as $iface) {
            $type = $iface['_technology'];
            if (!$loadOnly || in_array("rx", $loadOnly)) {
                if ($type == 'epon' && $this->isEponCardsExist()) $addOid('epon.optical.rx', $iface);
                if ($type == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.optical.rx', $iface);
            }
            if (!$loadOnly || in_array("tx", $loadOnly)) {
                if ($type == 'epon' && $this->isEponCardsExist()) $addOid('epon.optical.tx', $iface);
                if ($type == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.optical.tx', $iface);
            }
            if (!$loadOnly || in_array("voltage", $loadOnly)) {
                if ($type == 'epon' && $this->isEponCardsExist()) $addOid('epon.optical.voltage', $iface);
                if ($type == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.optical.voltage', $iface);
            }
            if (!$loadOnly || in_array("temp", $loadOnly)) {
                if ($type == 'epon' && $this->isEponCardsExist()) $addOid('epon.optical.temp', $iface);
                if ($type == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.optical.temp', $iface);
            }
            if (!$loadOnly || in_array("distance", $loadOnly)) {
                if ($type == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.optical.distance', $iface);
            }
            if (!$loadOnly || in_array("olt_rx", $loadOnly)) {
                $addOid('gpon.optical.olt_rx', $iface);
            }
        }

        $this->_mustLoadedOidNames = $loadingOidNames;

        $this->response = $this->formatResponse($this->snmp->get($oids, 5, 2));
        return $this;
    }

    function prepareInterfacesList($interface = null)
    {
        $ifaces = [];
        if ($interface) {
            $ifaces = $this->getModule('pon_onts_status')->run(['load_only' => 'status', 'interface' => $interface])->getPrettyFiltered(['load_only' => 'status', 'interface' => $interface]);
        } else {
            $ifaces = $this->getModule('pon_onts_status')->run(['load_only' => 'status', 'interface' => null])->getPrettyFiltered(['load_only' => 'status', 'interface' => null]);
        }
        $returningIfaces = [];
        foreach ($ifaces as $iface) {
            if ($iface['status'] !== 'Online') {
                continue;
            }
            $returningIfaces[$iface['interface']['_oid_id']] = $iface['interface'];
        }
        return $returningIfaces;
    }

    function parseOpticalResponse()
    {
        $ifaces = [];
        if (isset($this->_mustLoadedOidNames['epon.optical.temp'])) {
            $responses = $this->getResponseByName('epon.optical.temp')->fetchAll();
            foreach ($responses as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['temp'] = round($resp->getParsedValue(), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['epon.optical.voltage'])) {
            $responses = $this->getResponseByName('epon.optical.voltage')->fetchAll();
            foreach ($responses as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['voltage'] = round($resp->getParsedValue(), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['epon.optical.tx'])) {
            $responses = $this->getResponseByName('epon.optical.tx')->fetchAll();
            foreach ($responses as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['tx'] = round($resp->getParsedValue(), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['epon.optical.rx'])) {
            $responses = $this->getResponseByName('epon.optical.rx')->fetchAll();
            foreach ($responses as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['rx'] = round($resp->getParsedValue(), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.rx'])) {
            $responses = $this->getResponseByName('gpon.optical.rx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $val = $resp->getParsedValue();
                $ifaces[$iface['id']]['rx'] = round($val == 65535 ? null : (($val > 30000) ? ($val - 65536) * 0.002 - 30 : $val * 0.002 - 30), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.tx'])) {
            $responses = $this->getResponseByName('gpon.optical.tx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $val = $resp->getParsedValue();
                $ifaces[$iface['id']]['tx'] = round($val == 65535 ? null : (($val > 30000) ? ($val - 65536) * 0.002 - 30 : $val * 0.002 - 30), 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.olt_rx'])) {
            $responses = $this->getResponseByName('gpon.optical.olt_rx')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid());
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['olt_rx'] = round($resp->getParsedValue() / 1000, 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.temp'])) {
            $responses = $this->getResponseByName('gpon.optical.temp')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['temp'] = round($resp->getParsedValue() / 256, 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.voltage'])) {
            $responses = $this->getResponseByName('gpon.optical.voltage')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 2) . "." . Helper::getIndexByOid($resp->getOid(), 1);
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['voltage'] = round($resp->getParsedValue() * 0.02, 3);
            }
        }
        if (isset($this->_mustLoadedOidNames['gpon.optical.distance'])) {
            $responses = $this->getResponseByName('gpon.optical.distance')->fetchAll();
            foreach ($responses as $resp) {
                $id = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid());
                $iface = $this->parseInterface($id);
                if (!isset($ifaces[$iface['id']])) {
                    $ifaces[$iface['id']] = [
                        'interface' => $iface,
                    ];
                }
                $ifaces[$iface['id']]['distance'] = $resp->getParsedValue();
            }
        }
        return array_values($ifaces);
    }

}

