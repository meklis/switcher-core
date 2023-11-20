<?php


namespace SwitcherCore\Modules\BDcom;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class LinkInfo extends BDcomAbstractModule
{
    protected function formate()
    {
        $snmp_high_speed = !$this->response['if.HighSpeed']->error() ? $this->response['if.HighSpeed']->fetchAll() : [];
        $snmp_type = !$this->response['if.Type']->error() ? $this->response['if.Type']->fetchAll() : [];
        $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
        $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];
        $snmp_duplex = !$this->response['if.StatsDuplexStatus']->error() ? $this->response['if.StatsDuplexStatus']->fetchAll() : [];

        $indexes = [];
        foreach ($this->getPhysicalInterfaces() as $index => $port) {
            $indexes[$index]['interface'] = $port;
            $indexes[$index]['oper_status'] = null;
            $indexes[$index]['nway_status'] = null;
            $indexes[$index]['admin_state'] = null;
            $indexes[$index]['last_change'] = null;
            $indexes[$index]['medium_type'] = null;
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
        foreach ($snmp_duplex as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            if ($indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] == 'Down' || $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] == 'LLDown') {
                $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = 'Down';
                continue;
            }
            if ($index->getParsedValue() == 'Down') {
                $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = $index->getParsedValue();
            } else {
                $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] .= "-" . $index->getParsedValue();
            }
        }

        foreach ($snmp_admin_status as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] = $index->getParsedValue();
        }
        foreach ($snmp_type as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $type = $index->getParsedValue();
            if($index->getParsedValue() == 1) {
                $type = 'PON';
            }
            $indexes[Helper::getIndexByOid($index->getOid())]['type'] = $type;
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
            $this->oids->getOidByName('if.HighSpeed')->getOid(),
            $this->oids->getOidByName('if.Type')->getOid(),
            $this->oids->getOidByName('if.OperStatus')->getOid(),
            $this->oids->getOidByName('if.AdminStatus')->getOid(),
            $this->oids->getOidByName('if.StatsDuplexStatus')->getOid(),
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