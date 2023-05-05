<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends ModuleAbstract
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
        $data = $this->getResponseByName('epon.ont.macAddr', $resp);
        if (!$data->error()) {
            foreach ($data->fetchAll() as $d) {
                $xid = Helper::getIndexByOid($d->getOid());
                $ifaces[$xid] = [
                    'interface' => $this->parseInterface($xid),
                    'mac_address' => Helper::formatMac3Blocks($d->getHexValue()),
                ];
            }
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
        $oidRequests[] = $this->oids->getOidByName('epon.ont.macAddr');
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

