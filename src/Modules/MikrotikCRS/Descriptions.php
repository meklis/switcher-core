<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Descriptions extends \SwitcherCore\Modules\General\Switches\Descriptions
{
    use InterfacesTrait;

    protected function formate() {
        $descr = !$this->response['if.Name']->error() ? $this->response['if.Name']->fetchAll() : [];

        $indexes = [];
        foreach ($this->getInterfacesIds() as $index=>$port) {
            $indexes[$index]['interface'] = $port;
            $indexes[$index]['description'] = null;
        }
        foreach ($descr as $index) {
            if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            $indexes[Helper::getIndexByOid($index->getOid())]['description'] =  $index->getValue();
        }
        return $indexes;
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();
        if($filter['type']) {
            $types = explode(",", $filter['type']);
            foreach ($response as $num => $resp) {
                if(!isset($resp['type']))  {
                    unset($response[$num]);
                    continue;
                }
                if (!in_array($resp['type'], $types)) {
                    unset($response[$num]);
                }
            }
        }
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($response as $num=>$resp) {
                if(!isset($resp['interface']))  {
                    unset($response[$num]);
                    continue;
                }
                if($interface['id'] != $resp['interface']['id']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }
    public function run($filter = [])
    {
        $data = [
            $this->oids->getOidByName('if.Name')->getOid(),
        ];

        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($data as $num => $d) {
                $data[$num] .= ".{$interface['_snmp_id']}";
            }
        }
        $oidObjects = [];
        foreach ($data as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
