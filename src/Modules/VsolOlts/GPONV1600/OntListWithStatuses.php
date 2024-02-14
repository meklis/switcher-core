<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


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
        foreach ($this->getOntInterfaces() as $v) {
            if ($v['type'] !== 'ONU') continue;
            $ifaces[$v['xid']] = [
                'interface' => $v,
                'status' => null,
                'admin_state' => null,
                'bind_status' => null,
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
        try {
            $data = $this->getResponseByName('ont.status', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface  = $this->parseInterface(Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid()));
                    if (isset($ifaces[$iface['xid']])) {
                        $ifaces[$iface['xid']]['bind_status'] = $d->getParsedValue();
                    }
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
                $oidRequests[] = $this->oids->getOidByName('if.AdminStatus');
            }
            if (in_array('status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('if.OperStatus');
            }
            if (in_array('bind_status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.status');
            }
        } else {
            $oidRequests = [
                $this->oids->getOidByName('if.AdminStatus'),
                $this->oids->getOidByName('if.OperStatus'),
                $this->oids->getOidByName('ont.status'),
            ];
        }
        $oids = [];
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            foreach ($oidRequests as $oid) {
                $index = "." . $iface['xid'];
                if($oid->getName() === 'ont.status') {
                    $index = $iface['_snmp_id'];
                }
                $oids[] = \SnmpWrapper\Oid::init($oid->getOid() . $index);
            }
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function (Oid $e) {
                return \SnmpWrapper\Oid::init($e->getOid());
            }, $oidRequests);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));

        }
        return $this;
    }
}

