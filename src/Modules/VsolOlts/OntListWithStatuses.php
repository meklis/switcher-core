<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends VsolOltsAbstractModule
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
        foreach ($this->getInterfaces() as $v) {
            if ($v['type'] !== 'ONU') continue;
            $ifaces[$v['xid']] = [
                'interface' => $v,
                'status' => null,
                'admin_state' => null,
            ];
        }
        try {
            $data = $this->getResponseByName('if.OperStatus', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (isset($ifaces[$xid])) {
                        if ($ifaces[$xid]['interface']['type'] == 'ONU') {
                            $status = $d->getParsedValue() === 'Up' ? 'Online' : 'Offline';
                        } else {
                            $status = $d->getParsedValue();
                        }
                        $ifaces[$xid]['status'] = $status;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('if.AdminStatus', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (isset($ifaces[$xid])) {
                        $ifaces[$xid]['admin_state'] = $d->getParsedValue();
                    }
                }
            }
        } catch (\Exception $e) {
        }


        return array_values(array_filter($ifaces, function ($e) {
            return $e['status'] != null || $e['admin_state'] != null;
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
                $oidRequests[] = $this->oids->getOidByName('if.AdminStatus');
            }
            if (in_array('status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('if.OperStatus');
            }
        } else {
            $oidRequests = [
                $this->oids->getOidByName('if.AdminStatus'),
                $this->oids->getOidByName('if.OperStatus'),
            ];
        }
        $oids = [];
        foreach ($oidRequests as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
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
}

