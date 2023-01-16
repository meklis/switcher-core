<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends GCOMAbstractModule
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
        $data = $this->getResponseByName('ont.opStatus', $resp);
        foreach ($data->fetchAll() as $d) {
            $iface = $this->parseInterface($this->getOnuXidByOid($d->getOid()));
            if ($iface['type'] === 'ONU') {
                $status = $d->getParsedValue() === 'Up' ? 'Online' : 'Offline';
            } else {
                $status = $d->getParsedValue();
            }
            $ifaces[$iface['id']]['status'] = $status;
            $ifaces[$iface['id']]['interface'] = $iface;
        }
        return array_values($ifaces);
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
            if (in_array('status', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.opStatus');
            }
        } else {
            $oidRequests = [
                $this->oids->getOidByName('ont.opStatus'),
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

