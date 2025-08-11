<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\AbstractModule;

class DirectRoutes extends AbstractModule {
    public function run($params = []) {
        $oids[] = $this->oids->getOidByName('ipAddrTable.ipAdEntIfIndex')->getOid();
        $oids[] = $this->oids->getOidByName('ipAddrTable.ipAdEntNetMask')->getOid();
        $oids[] = $this->oids->getOidByName('if.Descr')->getOid();
        $oids[] = $this->oids->getOidByName('if.Alias')->getOid();

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }

        $res = $this->formatResponse($this->snmp->walk($oidObjects));
        foreach($res as $name => $r) {
            if($res[$name]->error()) {
                throw new \Exception("Returned error {$res[$name]->error()} from {$res[$name]->getRaw()->ip}");
            }
        }

        $networks = [];
        while ($d = $res['ipAddrTable.ipAdEntIfIndex']->fetchOne()) {
            $gateway = Helper::oid2IP($d->getOid());
            $networks[$gateway]['id'] = $d->getValue();
        }
        while ($d = $res['ipAddrTable.ipAdEntNetMask']->fetchOne()) {
            $gateway = Helper::oid2IP($d->getOid());
            $networks[$gateway]['netmask'] = $d->getValue();
        }
        $vlans = [];
        while ($d = $res['if.Descr']->fetchOne()) {
            if(!preg_match('/^Vlan\s?(\d{1,4})$/i', $d->getValue(), $m)) continue;
            $if_id = Helper::getIndexByOid($d->getOid());
            $vlans[$if_id]['id'] = $m[1];
        }
        while ($d = $res['if.Alias']->fetchOne()) {
            $if_id = Helper::getIndexByOid($d->getOid());
            $vlans[$if_id]['name'] = $d->getValue();
        }

        $this->response = [];
        foreach($networks as $gateway => $arr) {
            if(!isset($vlans[$arr['id']]['id'])) continue;
            $subnet = long2ip(ip2long($gateway) & ip2long($arr['netmask']));
            $cmask = 32 - log((ip2long($arr['netmask']) ^ ip2long('255.255.255.255')) + 1, 2);
            $wcmask = long2ip( ~ip2long($arr['netmask']) );
            $fsubnet = sprintf("%s/%s", $subnet, $cmask);
            $bcast = long2ip(ip2long($gateway) | ip2long($wcmask));
            
            $this->response[] = [
                'type' => 'v4',
                'network' => $subnet,
                'broadcast' => $bcast,
                'gateway' => $gateway,
                'cidr' => $fsubnet,
                'vlan_id' => $vlans[$arr['id']]['id'],
                'vlan_name' => (isset($vlans[$arr['id']]['name']) ? $vlans[$arr['id']]['name'] : null),
            ];
        }
        
        return $this;
    }

    public function getPretty() {
        return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }
}