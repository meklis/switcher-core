<?php


namespace SwitcherCore\Modules\GCOM;


use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\GCOM\GCOMAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class LinkInfo extends GCOMAbstractModule
{

    protected function formate()
    {
        $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
        $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];

        $indexes = [];
        foreach ($this->getPhysicalInterfaces() as  $port) {
            $indexes[$port['xid']]['interface'] = $port;
            $indexes[$port['xid']]['oper_status'] = null;
            $indexes[$port['xid']]['nway_status'] = null;
            $indexes[$port['xid']]['admin_state'] = null;
            $indexes[$port['xid']]['last_change'] = null;
            $indexes[$port['xid']]['medium_type'] = null;
            $indexes[$port['xid']]['type'] = $port['type'];
        }

        foreach ($snmp_oper_status as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] = $index->getParsedValue();
            $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = $index->getParsedValue();
        }

        foreach ($snmp_admin_status as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] = $index->getParsedValue();
        }
        return $indexes;
    }

    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();
        if ($filter['type']) {
            $types = explode(",", $filter['type']);
            foreach ($response as $num => $resp) {
                if (!isset($resp['type'])) {
                    unset($response[$num]);
                    continue;
                }
                if (!in_array($resp['type'], $types)) {
                    unset($response[$num]);
                }
            }
        }
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($response as $num => $resp) {
                if (!isset($resp['interface'])) {
                    unset($response[$num]);
                    continue;
                }
                if ($interface['id'] != $resp['interface']['id']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }

    public function run($filter = [])
    {
        $data = [
            $this->oids->getOidByName('if.OperStatus')->getOid(),
            $this->oids->getOidByName('if.AdminStatus')->getOid(),
        ];

        $oids = [];
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($data as  $oid) {
                $oids[] = Oid::init($oid . ".{$interface['xid']}");
            }
        } else {
            foreach ($this->getPhysicalInterfaces() as $phyIface) {
                foreach ($data as $oid) {
                    $oids[] =  Oid::init($oid . ".{$phyIface['xid']}");
                }
            }
        }
        $this->response = $this->formatResponse($this->snmp->get($oids));
        return $this;
    }

}

