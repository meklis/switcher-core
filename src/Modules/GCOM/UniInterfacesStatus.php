<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesStatus extends GCOMAbstractModule
{
    /**
     * @var SnmpResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }


    function getPretty()
    {
        $data = $this->getResponseByName('ont.uni.opStatus');
        if ($data->error()) {
            throw new \SNMPException($data->error());
        }
        $ifaces = [];
        foreach ($data->fetchAll() as $r) {
            $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid(), 1));
            $uni = Helper::getIndexByOid($r->getOid());
            $ifaces[$iface['id']]['interface'] = $iface;

            $ifaces[$iface['id']]['unis'][$uni] = [
                'num' => (int)$uni,
                'status' => $r->getParsedValue(),
            ];
        }

        $data = $this->getResponseByName('ont.uni.adminStatus');
        if ($data->error()) {
            throw new \SNMPException($data->error());
        }
        foreach ($data->fetchAll() as $r) {
            $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid(), 1));
            $uni = Helper::getIndexByOid($r->getOid());
            $ifaces[$iface['id']]['interface'] = $iface;

            $ifaces[$iface['id']]['unis'][$uni]['admin_state'] = $r->getParsedValue();
        }

        return array_values(array_map(function ($e) {
            if (isset($e['unis'])) {
                $e['unis'] = array_values($e['unis']);
            }
            return $e;
        }, $ifaces));
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oidList[] = $this->oids->getOidByName('ont.uni.opStatus');
        $oidList[] = $this->oids->getOidByName('ont.uni.adminStatus');
        $oids = [];
        foreach ($oidList as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
        }
        $oids = array_map(function ($e) {
            return \SnmpWrapper\Oid::init($e);
        }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
}

