<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends HuaweiOLTAbstractModule
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
        $return = [];
        try {
            $data = $this->getResponseByName('ont.status', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." .Helper::getIndexByOid($d->getOid());
                    $iface = $this->parseInterface($xid);
                    $return[$xid] = [
                        'interface' => $iface,
                        'status' => $d->getParsedValue(),
                        'conf_status' => null,
                        'admin_state' => null,
                        'bind_status' => null,
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('ont.confStatus', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." .Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['conf_status'] = $d->getParsedValue();
                    }
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.controlActive', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." .Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['admin_state'] = $d->getParsedValue();
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $return;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        $oidRequests = [];
        if (isset($filter['load_only']) && $filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
            if (in_array('conf_status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.confStatus');
            }
            if (in_array('status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.status');
            }
            if (in_array('admin_state', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.controlActive');
            }
        } else {
            $oidRequests = [
                $this->oids->getOidByName('ont.confStatus'),
                $this->oids->getOidByName('ont.status'),
                $this->oids->getOidByName('ont.controlActive'),
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
            $response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));
        }

        $loadOnly = ['bind_status'];
        if(isset($filter['load_only']) && $filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
        }

        if(in_array('bind_status', $loadOnly)) {
            $this->fillBindStatuses($response);
        }
        $this->response = array_values($response);

        return $this;
    }

    function fillBindStatuses(&$statuses)
    {
        $oid = $this->oids->getOidByName('ont.lastDownCause')->getOid();
        $oids = [];
        foreach ($statuses as $index=>$status) {
            if($status['status'] != 'Online') {
                $oids[] = \SnmpWrapper\Oid::init("{$oid}.{$status['interface']['xid']}");
            } else {
                $statuses[$index]['bind_status'] = $status['status'];
            }
        }
        if(!$oids) {
            return [];
        }
        $responses = $this->formatResponse($this->snmp->get($oids));
        if(!isset($responses['ont.lastDownCause'])) {
            throw new \Exception("Not found responses for ont.lastDownCause");
        } elseif ($responses['ont.lastDownCause']->error()) {
            throw new \Exception($responses['ont.lastDownCause']->error());
        }
        foreach ($responses['ont.lastDownCause']->fetchAll() as $resp) {
            $xid = Helper::getIndexByOid($resp->getOid(), 1) . "." .Helper::getIndexByOid($resp->getOid());
            if($resp->getParsedValue() !== 'Unknown') {
                $statuses[$xid]['bind_status'] = $resp->getParsedValue();
            }
        }
        return  $statuses;
    }
}

