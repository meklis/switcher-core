<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
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

        if ($this->isHasGponIfaces()) {
            try {
                $data = $this->getResponseByName('ont.gpon.status', $resp);
                if ($data->error()) {
                    throw new Exception("ont.gpon.status -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    $iface = $this->parseInterface($xid);
                    $return[$xid] = [
                        'interface' => $iface,
                        'status' => $d->getParsedValue(),
                        'conf_status' => null,
                        'admin_state' => null,
                        'bind_status' => null,
                    ];
                }
            } catch (IncompleteResponseException $e) {
            }

            try {
                $data = $this->getResponseByName('ont.gpon.confStatus', $resp);
                if ($data->error()) {
                    throw new Exception("ont.gpon.confStatus -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['conf_status'] = $d->getParsedValue();
                    }
                }
            } catch (IncompleteResponseException $e) {
            }

            try {
                $data = $this->getResponseByName('ont.gpon.controlActive', $resp);
                if ($data->error()) {
                    throw new Exception("ont.gpon.controlActive -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['admin_state'] = $d->getParsedValue();
                    }
                }
            } catch (IncompleteResponseException $e) {
            }
        }

        if ($this->isHasEponIfaces()) {
            try {
                $data = $this->getResponseByName('ont.epon.status', $resp);
                if ($data->error()) {
                    throw new Exception("ont.epon.status -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    $iface = $this->parseInterface($xid);
                    $return[$xid] = [
                        'interface' => $iface,
                        'status' => $d->getParsedValue(),
                        'conf_status' => null,
                        'admin_state' => null,
                        'bind_status' => null,
                    ];
                }
            } catch (IncompleteResponseException $e) {
            }

            try {
                $data = $this->getResponseByName('ont.epon.confStatus', $resp);
                if ($data->error()) {
                    throw new Exception("ont.epon.confStatus -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['conf_status'] = $d->getParsedValue();
                    }
                }
            } catch (IncompleteResponseException $e) {}

            try {
                $data = $this->getResponseByName('ont.epon.controlActive', $resp);
                if ($data->error()) {
                    throw new Exception("ont.epon.controlActive -> " . $data->error());
                }
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (isset($return[$xid])) {
                        $return[$xid]['admin_state'] = $d->getParsedValue();
                    }
                }
            } catch (IncompleteResponseException $e) {}
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
        if((!isset($filter['load_only']) || !$filter['load_only']) && !$filter['interface']) {
            $filter['load_only'] = 'status,admin_state,bind_status';
        }

        if (isset($filter['load_only']) && $filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
            if (in_array('conf_status', $loadOnly)) {
                if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.confStatus');
                if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.confStatus');
            }
            if (in_array('status', $loadOnly)) {
                if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.status');
                if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.status');
            }
            if (in_array('admin_state', $loadOnly)) {
                if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.controlActive');
                if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.controlActive');
            }
        } else {
            $oidRequests = [];
            if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.confStatus');
            if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.status');
            if ($this->isHasGponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.gpon.controlActive');

            if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.confStatus');
            if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.status');
            if ($this->isHasEponIfaces()) $oidRequests[] = $this->oids->getOidByName('ont.epon.controlActive');
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oidRequests = array_filter($oidRequests, function (Oid $e) use ($iface) {
                return strpos($e->getName(), $iface['_technology']) !== false;
            });
            $oids = array_map(function (Oid $e) use ($iface) {
                return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['xid']);
            }, $oidRequests);
            $response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function (Oid $e) {
                return \SnmpWrapper\Oid::init($e->getOid());
            }, $oidRequests);
            $response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));

        }
        $loadOnly = ['bind_status'];
        if (isset($filter['load_only']) && $filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
        }

        if (in_array('bind_status', $loadOnly)) {
            $this->fillBindStatuses($response);
        }
        $this->response = array_values($response);

        return $this;
    }

    function fillBindStatuses(&$statuses)
    {
        $gponDownCause = $this->oids->getOidByName('ont.gpon.lastDownCause')->getOid();
        $eponDownCause = $this->oids->getOidByName('ont.epon.lastDownCause')->getOid();
        $oids = [];
        foreach ($statuses as $index => $status) {
            if ($status['status'] != 'Online') {
                if ($status['interface']['_technology'] === 'gpon') {
                    $oid = $gponDownCause;
                } else {
                    $oid = $eponDownCause;
                }
                $oids[] = \SnmpWrapper\Oid::init("{$oid}.{$status['interface']['xid']}");
            } else {
                $statuses[$index]['bind_status'] = $status['status'];
            }
        }
        if (!$oids) {
            return [];
        }
        $responses = $this->formatResponse($this->snmp->get($oids));
        if (isset($responses['ont.gpon.lastDownCause']) && !$responses['ont.gpon.lastDownCause']->error()) {
            foreach ($responses['ont.gpon.lastDownCause']->fetchAll() as $resp) {
                $xid = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid());
                if ($resp->getParsedValue() !== 'Unknown') {
                    $statuses[$xid]['bind_status'] = $resp->getParsedValue();
                }
            }
        }
        if (isset($responses['ont.epon.lastDownCause']) && !$responses['ont.epon.lastDownCause']->error()) {
            foreach ($responses['ont.epon.lastDownCause']->fetchAll() as $resp) {
                $xid = Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid());
                if ($resp->getParsedValue() !== 'Unknown') {
                    $statuses[$xid]['bind_status'] = $resp->getParsedValue();
                }
            }
        }

        return $statuses;
    }
}

