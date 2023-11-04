<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $fdb_port =  $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        $fdb_status =  $this->oids->getOidByName('dot1q.FdbStatus')->getOid();
        if($filter['vlan_id']) {
            $fdb_port .= ".{$filter['vlan_id']}";
            $fdb_status .= ".{$filter['vlan_id']}";
        }
        if($filter['vlan_id'] && $filter['mac']) {
            $fdb_port .= "." . Helper::mac2oid($filter['mac']);
            $fdb_status .= "." .   Helper::mac2oid($filter['mac']);
        }
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($fdb_status), Oid::init($fdb_port),
        ]));
        return $this;
    }

    function getPrettyFiltered($filter = [])
    {
        $data = parent::getPrettyFiltered($filter);
        return array_filter($data, function ($f) {
            return $f['status'] !== 'SELF';
        });
    }


}
