<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends ModuleAbstract
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
        return $this->response;
    }

    protected function formate($resp)
    {
        $ifaces = [];
        if (in_array('gpon.ont.phaseState', $this->_mustLoadOidNames)) {
            $data = $this->getResponseByName('gpon.ont.phaseState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['status'] = $d->getParsedValue() == 'Working' ? 'Online' : 'Offline';
                    $ifaces[$xid]['bind_status'] = $d->getParsedValue();
                }
            }
        }
        if (in_array('epon.ont.mgmtOnlineStatus', $this->_mustLoadOidNames)) {
            $data = $this->getResponseByName('epon.ont.mgmtOnlineStatus', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['status'] = $d->getParsedValue() == 'online' ? 'Online' : 'Offline';
                    $ifaces[$xid]['bind_status'] = $d->getParsedValue();
                }
            }
        }
        if (in_array('epon.ont.lastOfflineReason', $this->_mustLoadOidNames)) {
            $data = $this->getResponseByName('epon.ont.lastOfflineReason', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    if($d->getParsedValue() == 0) {
                        continue;
                    }
                    $ifaces[$xid]['bind_status'] = $ifaces[$xid]['status'] === 'Offline' ? $d->getParsedValue() : 'Online';
                }
            }
        }
        if (in_array('gpon.ont.adminState', $this->_mustLoadOidNames)) {
            $data = $this->getResponseByName('gpon.ont.adminState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['admin_state'] = $d->getParsedValue();
                }
            }
        }
        if (in_array('epon.ont.adminState', $this->_mustLoadOidNames)) {
            $data = $this->getResponseByName('epon.ont.adminState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['admin_state'] = $d->getParsedValue();
                }
            }
        }
        $c =  array_values(array_filter($ifaces, function ($e) {
            return $e['status'] != null || $e['admin_state'] != null || $e['bind_status'] != null;
        }));
        if(count($c) == 0) {
            throw new \Exception("Error get ONT status information, try repeat");
        }
        return $c;
    }

    protected $_mustLoadOidNames = [];

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        $oidRequests = [];
        $loadingOidNames = [];
        $addOid = function ($oidName) use (&$loadingOidNames, &$oidRequests) {
            $oidRequests[] = $this->oids->getOidByName($oidName);
            $loadingOidNames[] = $oidName;
        };
        if ($filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
            if (in_array('admin_state', $loadOnly)) {
                if ($this->isGponCardsExist()) $addOid('gpon.ont.adminState');
                if ($this->isEponCardsExist()) $addOid('epon.ont.adminState');
            }
            if (in_array('status', $loadOnly)) {
                if ($this->isGponCardsExist()) $addOid('gpon.ont.phaseState');
                if ($this->isEponCardsExist()) $addOid('epon.ont.mgmtOnlineStatus');
            }
            if (in_array('bind_status', $loadOnly)) {
                if ($this->isEponCardsExist()) $addOid('epon.ont.lastOfflineReason');
            }
        } else {
         //   if ($this->isGponCardsExist()) $addOid('gpon.ont.adminState');
            if ($this->isGponCardsExist()) $addOid('gpon.ont.phaseState');
            if ($this->isEponCardsExist()) $addOid('epon.ont.mgmtOnlineStatus');
            if ($this->isEponCardsExist()) $addOid('epon.ont.adminState');
            if ($this->isEponCardsExist()) $addOid('epon.ont.lastOfflineReason');
        }
        $this->_mustLoadOidNames = $loadingOidNames;
        $oids = [];
        foreach ($oidRequests as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['_oid_id'];
            }, $oids);
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));
        }
        return $this;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }
}

