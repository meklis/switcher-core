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

        return array_values($return);
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
            if (in_array('conf_status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.confStatus');
            }
            if (in_array('status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.status');
            }
        } else {
            $oidRequests = [
                $this->oids->getOidByName('ont.confStatus'),
                $this->oids->getOidByName('ont.status'),
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

