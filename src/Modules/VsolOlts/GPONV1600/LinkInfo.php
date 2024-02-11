<?php

namespace SwitcherCore\Modules\VsolOlts\GPONV1600;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class LinkInfo extends VsolOltsAbstractModule
{
    protected function formate()
    {
        $snmp_high_speed = !$this->response['if.HighSpeed']->error() ? $this->response['if.HighSpeed']->fetchAll() : [];
        $snmp_last_change = !$this->response['if.LastChange']->error() ? $this->response['if.LastChange']->fetchAll() : [];
        $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
        $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];

        $indexes = [];

        foreach ($this->getPhysicalInterfaces() as $index => $port) {
            $xid = $port['xid'];
            $indexes[$xid]['interface'] = $port;
            $indexes[$xid]['oper_status'] = null;
            $indexes[$xid]['nway_status'] = null;
            $indexes[$xid]['admin_state'] = null;
            $indexes[$xid]['last_change'] = null;
            $indexes[$xid]['medium_type'] = null;
        }


        foreach ($snmp_oper_status as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] = $index->getParsedValue();
        }

        foreach ($snmp_high_speed as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            if ($indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] == 'Down' || $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] == 'LLDown') {
                $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = 'Down';
                continue;
            }
            $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = $index->getParsedValue();
        }

        foreach ($snmp_admin_status as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] = $index->getParsedValue();
        }

        foreach ($snmp_last_change as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            if ($index->getValue()) {
                $indexes[Helper::getIndexByOid($index->getOid())]['last_change'] = $index->getValueAsTimeTicks();
            }
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
        $data = [];
        $data_oids = [
            $this->oids->getOidByName('if.HighSpeed')->getOid(),
            $this->oids->getOidByName('if.LastChange')->getOid(),
            $this->oids->getOidByName('if.OperStatus')->getOid(),
            $this->oids->getOidByName('if.AdminStatus')->getOid(),
        ];

        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($data_oids as $num => $d) {
                $data[$num] .= ".{$interface['xid']}";
            }
        } else {
            foreach ($data_oids as $num => $d) {
                foreach ($this->getPhysicalInterfaces() as $item) {
                    $data[] = $data_oids[$num] . ".{$item['xid']}";
                }
            }
        }
        $oidObjects = [];

        foreach ($data as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->get($oidObjects));
        return $this;
    }

}