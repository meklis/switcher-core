<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesStatus extends HuaweiOLTAbstractModule
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
        try {
            $data = $this->getResponseByName('uni.gpon.status');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['status'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.configure.state');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['admin_state'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.configure.flowControl');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['flow_control'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.configure.vlan');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['vlan'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.configure.speed');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['speed'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.configure.duplex');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['duplex'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.gpon.type');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['type'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}

        try {
            $data = $this->getResponseByName('uni.epon.status');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['status'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.epon.configure.state');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['admin_state'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.epon.configure.flowControl');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['flow_control'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.epon.configure.vlan');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['vlan'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.epon.configure.speed');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['speed'] = $r->getParsedValue();
                }
            }
            $data = $this->getResponseByName('uni.epon.configure.duplex');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $uni = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['unis'][$uni]['num'] = $uni;
                    $ifaces[$iface['id']]['unis'][$uni]['duplex'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}

        return array_values(array_map(function ($e) {
            if (isset($e['unis'])) {
                $e['unis'] = array_values(array_filter($e['unis'], function ($e) {
                    if (isset($e['vlan']) && ($e['vlan'] == '-1' || (int)$e['vlan'] == -1)) {
                        return false;
                    }
                    if (isset($e['type']) && ($e['type'] == '-1' || (int)$e['type'] == -1)) {
                        return false;
                    }
                    return true;
                }));
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
            throw new \Exception("Interface is required parameter");
        }
        $iface = $this->parseInterface($filter['interface']);
        if($iface['_technology'] === 'gpon')  {
            $oidList = $this->oids->getOidsByRegex("uni\.{$iface['_technology']}*");
            $oids = array_map(function ($e) use ($iface) {
                return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['xid']);
            }, $oidList);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }  else {
            $oids = [];
            $countIfaces = $this->getCountEponIfaces($iface);
            $oidList = $this->oids->getOidsByRegex("uni\.{$iface['_technology']}*");
            for($i = 1; $i <= $countIfaces; $i++) {
                $oids = array_merge($oids, array_map(function ($e) use ($iface, $i) {
                    return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['xid'] . "." . $i);
                }, $oidList));
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        }
        return $this;
    }

    function getCountEponIfaces($iface)
    {
        $oidList = $this->oids->getOidsByRegex("uniif\.epon\.*");
        $oids = array_map(function ($e) use ($iface) {
            return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['xid']);
        }, $oidList);
        $data = $this->snmp->get($oids);
        $countIfaces = 0;
        foreach ($data as $d) {
            if($d->getError()) continue;
            foreach ($d->getResponse() as $resp) {
                if((int)$resp->getValue() > 0) $countIfaces += (int)$resp->getValue();
            }
        }
        return $countIfaces;
    }
}

