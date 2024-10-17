<?php


namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

abstract class NetSettings extends AbstractInterfaces
{
    protected function formate() {
        $response = [];
        foreach ($this->getResponseByName('ip.netSettings')->fetchAll() as $num => $dt) {
            $response['vlan_id'] = Helper::getIndexByOid($dt->getOid(), 4);
            $ip =
                Helper::getIndexByOid($dt->getOid(), 3) . "." .
                Helper::getIndexByOid($dt->getOid(), 2) . "." .
                Helper::getIndexByOid($dt->getOid(), 1) . "." .
                Helper::getIndexByOid($dt->getOid())
            ;
            switch ($num) {
                case 0:
                    $response['network'] = $ip;
                    break;
                case 1:
                    $response['gateway_ip'] = $ip;
                    $response['gateway_mac'] = $dt->getHexValue();
                    break;
                case 2:
                    $response['self_ip'] = $ip;
                    $response['self_mac'] = $dt->getHexValue();
                    break;
                case 3:
                    $response['broadcast'] = $ip;
                    break;
            }
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
        $oidObjects = Oid::init($this->oids->getOidByName('ip.netSettings'));
        $this->response = $this->formatResponse($this->snmp->walk([$oidObjects->getOid()]));
        return $this;
    }
}
