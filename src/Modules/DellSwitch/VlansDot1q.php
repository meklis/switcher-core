<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class VlansDot1q extends \SwitcherCore\Modules\General\Switches\VlansDot1q
{
    use InterfacesTrait;

    protected function formate() {
        $response = [];
        $forbidden = $this->getResponseByName('dot1q.VlanStaticForbiddenEgressPorts');
        $untagged = $this->getResponseByName('dot1q.VlanStaticUntaggedPorts');
        $names = $this->getResponseByName('dot1q.VlanStaticName');
        $egress = $this->getResponseByName('dot1q.VlanStaticEgressPorts');

        if($names->error()) {
            throw new IncompleteResponseException($names->error());
        }
        if($egress->error()) {
            throw new IncompleteResponseException($egress->error());
        }
        if($untagged->error()) {
            throw new IncompleteResponseException($untagged->error());
        }
        if($forbidden->error()) {
            throw new IncompleteResponseException($forbidden->error());
        }

        $indexes = [];
        foreach ($this->getInterfacesIds() as $iface) {
            $indexes[$iface['_dot1q_id']] = $iface;
        }
        $formater = function ($resp) use ($indexes) {
            $dex = Helper::hexToBinStr($resp->getHexValue());
            $ports = [];
            for($port = 1; $port < strlen($dex) ; $port++) {
                if($dex[$port] == '1' && isset($indexes[$port])) $ports[] = $indexes[$port];
            }
            return $ports;
        };

        $response = [];
        foreach ($names->fetchAll() as $resp) {
            $response[Helper::getIndexByOid($resp->getOid())]['name'] = $resp->getValue();
            $response[Helper::getIndexByOid($resp->getOid())]['id'] = Helper::getIndexByOid($resp->getOid());
        }

        foreach ($egress->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['egress'] = $formater($resp);
        }

        foreach ($untagged->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['untagged'] = $formater($resp);
        }
        foreach ($forbidden->fetchAll() as $resp) {
            $response[ Helper::getIndexByOid($resp->getOid())]['ports']['forbidden'] = $formater($resp);
        }
        foreach ($response as $vlan_id => $resp) {
            foreach ($resp['ports']['egress'] as $port) {
                if(in_array($port, $response[$vlan_id]['ports']['untagged'])) continue;
                if(in_array($port, $response[$vlan_id]['ports']['forbidden'])) continue;
                $response[$vlan_id]['ports']['tagged'][] = $port;
            }
        }
        return array_values($response);
    }


}
