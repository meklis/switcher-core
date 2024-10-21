<?php


namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class NetSettings extends AbstractModule
{
    protected function formate() {
        $response = [
            'vlan_id'=> null,
            'self_ip'=> null,
            'self_mac_address'=> null,
            'netmask'=> null,
            'gateway_ip' => null,
            'gateway_mac_address' => null,
        ];
        foreach ($this->getResponseByName('ip.netSettings')->fetchAll() as $dt) {
            $idx = Helper::getIndexByOid($dt->getOid(), 4);
            switch($idx) {
                case 1: $response['self_ip'] = $dt->getValue(); break;
                case 2: $response['vlan_id'] = $dt->getValue(); break;
                case 3: $response['netmask'] = $dt->getValue(); break;
            }
        };
        if(!$this->getResponseByName('sys.macAddr')->error()) {
            $response['self_mac_address'] = $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue();
        } elseif (!$this->getResponseByName('sys.macAddrV2')->error()) {
            $response['self_mac_address'] = $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue();
        }
        $arps = [];
        foreach ($this->getResponseByName('ip.arpTable')->fetchAll() as   $dt) {
            $vlanId = Helper::getIndexByOid($dt->getOid(), 4);
            $ip =
                Helper::getIndexByOid($dt->getOid(), 3) . "." .
                Helper::getIndexByOid($dt->getOid(), 2) . "." .
                Helper::getIndexByOid($dt->getOid(), 1) . "." .
                Helper::getIndexByOid($dt->getOid())
            ;

            $idx = Helper::getIndexByOid($dt->getOid(), 5);

            switch ($idx) {
                case 1: $arps[$ip]['vlan_id'] = $dt->getValue(); break;
                case 2: $arps[$ip]['mac_address'] = $dt->getHexValue(); break;
                case 3: $arps[$ip]['ip'] = $dt->getValue(); break;
                case 4: if(!in_array($dt->getValue(), [3,4])) unset($arps[$ip]); break;
            }
        }
        $response['_arps'] = array_values($arps);
        if(count($arps) > 0) {
            $response['gateway_ip'] = $response['_arps'][0]['ip'];
            $response['gateway_mac_address'] = $response['_arps'][0]['mac_address'];
        }
        return $response;
    }
    function getPretty()
    {
        return $this->formate();
    }


    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $oidObjects = [
            Oid::init($this->oids->getOidByName('ip.arpTable')->getOid()),
            Oid::init($this->oids->getOidByName('ip.netSettings')->getOid()),
            Oid::init($this->oids->getOidByName('sys.macAddr')->getOid()),
            Oid::init($this->oids->getOidByName('sys.macAddrV2')->getOid()),
        ];
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
