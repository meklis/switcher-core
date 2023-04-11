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
        try {
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
                    $ifaces[$xid]['status'] = $d->getParsedValue() == 'working' ? 'Online' : 'Offline';
                    $ifaces[$xid]['bind_status'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
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
        } catch (\Exception $e) {
        }
        try {
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
                    $ifaces[$xid]['bind_status'] = $ifaces[$xid]['status'] === 'Offline' ? $d->getParsedValue() : 'Online';
                }
            }
        } catch (\Exception $e) {
        }
        try {
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
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        try {
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
        } catch (\Exception $e) {
        }
        return array_values(array_filter($ifaces, function ($e) {
            return $e['status'] != null || $e['admin_state'] != null || $e['bind_status'] != null;
        }));
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        $oidRequests = [];
        if ($filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
            if (in_array('admin_state', $loadOnly)) {
                if ($this->isGponCardsExist()) $oidRequests[] = $this->oids->getOidByName('gpon.ont.adminState');
                if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.adminState');
            }
            if (in_array('status', $loadOnly)) {
                if ($this->isGponCardsExist()) $oidRequests[] = $this->oids->getOidByName('gpon.ont.phaseState');
                if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.mgmtOnlineStatus');
            }
            if (in_array('bind_status', $loadOnly)) {
                if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.lastOfflineReason');
            }
        } else {
            if ($this->isGponCardsExist()) $oidRequests[] = $this->oids->getOidByName('gpon.ont.adminState');
            if ($this->isGponCardsExist()) $oidRequests[] = $this->oids->getOidByName('gpon.ont.phaseState');
            if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.mgmtOnlineStatus');
            if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.adminState');
            if ($this->isEponCardsExist()) $oidRequests[] = $this->oids->getOidByName('epon.ont.lastOfflineReason');
        }
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

