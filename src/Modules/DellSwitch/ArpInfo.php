<?php

namespace SwitcherCore\Modules\DellSwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ArpInfo extends AbstractModule
{
    use InterfacesTrait;

    public function run($params = [])
    {
        $oid = $this->oids->getOidByName('ip.arp.macAddr')->getOid();

        $this->response = $this->formatResponse($this->snmp->walk([Oid::init($oid)]));
        return $this;
    }

    public function getPretty()
    {
        if ($this->response['ip.arp.macAddr']->error()) {
            throw new \SNMPException($this->response['ip.arp.macAddr']->error());
        }
        $vlanIds = $this->getVlanIdsMap();
        $response = [];
        foreach ($this->response['ip.arp.macAddr']->fetchAll() as $mac) {
            $ip = Helper::getIndexByOid($mac->getOid(), 3) . "." .
                Helper::getIndexByOid($mac->getOid(), 2) . "." .
                Helper::getIndexByOid($mac->getOid(), 1) . "." .
                Helper::getIndexByOid($mac->getOid())
            ;
            $vlanIdIndex = Helper::getIndexByOid($mac->getOid(), 4);

            $response[] = [
                'mac' => $mac->getHexValue(),
                'ip' => $ip,
                'vlan_id' => isset($vlanIds[$vlanIdIndex]) ? $vlanIds[$vlanIdIndex] : null,
                'interface' =>  null,
            ];
        }

        return $response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}