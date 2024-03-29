<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesVlans extends HuaweiOLTAbstractModule
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
        $data = [];
        $ifaces = [];
        $data = $this->getResponseByName('ont.gpon.uni.pvid');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if (!$r->getValue()) continue;
                $xid = Helper::getIndexByOid($r->getOid(), 1);
                $uni = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['unis'][$uni]['num'] = $uni;
                $ifaces[$xid]['unis'][$uni]['pvid'] = $r->getParsedValue();
            }
        }
        $data = $this->getResponseByName('ont.gpon.uni.pvidMode');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid(), 1);
                $uni = Helper::getIndexByOid($r->getOid());
                if (!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['unis'][$uni]['num'] = $uni;
                $ifaces[$xid]['unis'][$uni]['pvid_mode'] = $r->getParsedValue();
            }
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
        if (!$filter['interface']) {
            throw new \Exception("Interface is required");
        }
        $iface = $this->parseInterface($filter['interface']);

        $oidList[] = $this->oids->getOidByName("ont.${iface['_technology']}.uni.pvid");
        $oidList[] = $this->oids->getOidByName("ont.${iface['_technology']}.uni.pvidMode");
        $oids = array_map(function ($e) use ($iface) {
            return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['xid']);
        }, $oidList);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
}

