<?php


namespace SwitcherCore\Modules\CData;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class Vlans extends CDataAbstractModule
{
    function getPretty()
    {
        return $this->getPrettyFiltered();
    }

    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $ports = [];
        foreach ($this->getResponseByName('vlan.tagged')->fetchAll() as $resp) {
            $vlan_id = Helper::getIndexByOid($resp->getOid(), 1);
            $ports[$vlan_id]['tagged'] = $this->parsePortsFromHex($resp->getHexValue());
        }
        foreach ($this->getResponseByName('vlan.untagged')->fetchAll() as $resp) {
            $vlan_id = Helper::getIndexByOid($resp->getOid(), 1);
            $ports[$vlan_id]['untagged'] = $this->parsePortsFromHex($resp->getHexValue());
        }
        $response = [];
        foreach ($this->getResponseByName('vlan.list')->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid(), 1);
            if(!isset($ports[$id])) {
                $ports[$id] = [
                    'egress' => [],
                    'untagged' => [],
                    'forbidden' => [],
                    'tagged' => [],
                ];
            }
            if(!isset($ports[$id]['untagged'])) $ports[$id]['untagged'] = [];
            if(!isset($ports[$id]['untagged'])) $ports[$id]['untagged'] = [];
            if(!isset($ports[$id]['forbidden'])) $ports[$id]['forbidden'] = [];
            $ports[$id]['egress'] = array_unique(array_merge($ports[$id]['untagged'],$ports[$id]['tagged']));
            $response[] = [
              'id' => $id,
              'name' => $resp->getValue(),
              'ports' => $ports[$id],
            ];
        }
        if($filter['vlan_id']) {
            $response = array_filter($response, function ($val) use ($filter) {
               return  $filter['vlan_id'] === $val['id'];
            });
        }
        return array_values($response);
    }
    function parsePortsFromHex($str) {
        $str = str_replace(':', "", $str);
        $blocks = str_split($str, 8);
        $response = [];
        foreach ($blocks as $block) {
            if(strlen($block) < 8) continue;
            $xId = hexdec(substr($block, 4,2));
            $iface = $this->parseInterface($xId);
            if(!$iface) continue;
            $response[] = $iface['name'];
        }
        return $response;
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $oids = $this->obj->oidCollector->getOidsByRegex('^vlan\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid(),true);
        }
        $this->response = $this->formatResponse($this->obj->walker->walk($oArray));
        return $this;
    }
}
