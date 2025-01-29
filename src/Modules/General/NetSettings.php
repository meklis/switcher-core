<?php


namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class NetSettings extends AbstractModule
{
    protected function formate() {
       return $this->response;
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
        try {
            $this->response = $this->searchByHextHop();
        } catch (\Exception $e) {
            $this->response = $this->searchByARPOnly();
        }
        return $this;
    }

    public function searchByHextHop() {
        //Get default route
        $resp = $this->snmp->get([
            Oid::init($this->oids->getOidByName('ip.routeTable.routes')->getOid() . ".0.0.0.0")
        ]);
        if($resp[0]->error) {
            throw new \Exception("HextHop Error: ".$resp[0]->error);
        }
        $gwIp = $resp[0]->getResponse()[0]->getValue();

        //Get mac address from arps
        $respArps = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('ip.arp.macAddr')->getOid())
        ]);
        if($respArps[0]->error) {
            throw new \Exception("ARP Error: ".$respArps[0]->error);
        }
        $gwMac = null;
        foreach ($respArps[0]->getResponse() as $arp) {
            $arpIp = Helper::getIndexByOid($arp->getOid(),3) . "."
                . Helper::getIndexByOid($arp->getOid(),2) . "."
                . Helper::getIndexByOid($arp->getOid(),1) . "."
                . Helper::getIndexByOid($arp->getOid());
            if($arpIp == $gwIp) {
                $gwMac = $arp->getHexValue();
                break;
            }
        }
        if(!$gwMac) {
            throw new \Exception("GW mac address not found");
        }

        return [
            'virtual_iface_id'=> null,
            'self_ip'=> $this->device->getIp(),
            'self_mac_address'=> null,
            'netmask'=> null,
            'gateway_ip' => $gwIp,
            'gateway_mac_address' => $gwMac,
        ];
    }


    function searchByARPOnly()
    {
        Helper::prepareFilter($filter);
        $oidObjects = [
            Oid::init($this->oids->getOidByName('ip.arp.macAddr')->getOid()),
            Oid::init($this->oids->getOidByName('ip.netSettings')->getOid()),
            Oid::init($this->oids->getOidByName('sys.macAddr')->getOid()),
            Oid::init($this->oids->getOidByName('sys.macAddrV2')->getOid()),
        ];
        $snmpResponses = $this->formatResponse($this->snmp->walk($oidObjects));

        $response = [
            'virtual_iface_id'=> null,
            'self_ip'=> null,
            'self_mac_address'=> null,
            'netmask'=> null,
            'gateway_ip' => null,
            'gateway_mac_address' => null,
        ];
        foreach ($this->getResponseByName('ip.netSettings', $snmpResponses)->fetchAll() as $dt) {
            $idx = Helper::getIndexByOid($dt->getOid(), 4);
            switch($idx) {
                case 1: $response['self_ip'] = $dt->getValue(); break;
                case 2: $response['virtual_iface_id'] = $dt->getValue(); break;
                case 3: $response['netmask'] = $dt->getValue(); break;
            }
        };
        if(!$this->getResponseByName('sys.macAddr', $snmpResponses)->error()) {
            $response['self_mac_address'] = $this->getResponseByName('sys.macAddr', $snmpResponses)->fetchOne()->getHexValue();
        } elseif (!$this->getResponseByName('sys.macAddrV2', $snmpResponses)->error()) {
            $response['self_mac_address'] = $this->getResponseByName('sys.macAddr', $snmpResponses)->fetchOne()->getHexValue();
        }
        $arps = [];
        foreach ($this->getResponseByName('ip.arp.macAddr', $snmpResponses)->fetchAll() as   $dt) {
            $vlanId = Helper::getIndexByOid($dt->getOid(), 4);
            $ip =
                Helper::getIndexByOid($dt->getOid(), 3) . "." .
                Helper::getIndexByOid($dt->getOid(), 2) . "." .
                Helper::getIndexByOid($dt->getOid(), 1) . "." .
                Helper::getIndexByOid($dt->getOid())
            ;

            $arps[$ip]['vlan_id'] = $vlanId;
            $arps[$ip]['ip'] = $ip;
            $arps[$ip]['mac_address'] = $dt->getHexValue();
        }
        $response['_arps'] = array_values($arps);
        if(count($arps) > 0) {
            foreach ($arps as $ip => $arp) {
                if($arp['mac_address'] === "FF:FF:FF:FF:FF:FF") {
                    continue;
                }
                if($arp['mac_address'] === $response['self_mac_address']) {
                    continue;
                }
                $response['gateway_ip'] = $arp['ip'];
                $response['gateway_mac_address'] = $arp['mac_address'];
            }
        }
        return $response;
    }
}
