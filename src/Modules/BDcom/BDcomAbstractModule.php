<?php

namespace SwitcherCore\Modules\BDcom;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

// EPON0/2 = 10200000 - PON port
// EPON0/2:3 = 10200300 - Port PORT with ONU
// EPON0/1:63 = 10106300 - Without UNI
// EPON0/1:63.1 = 10106301 - With UNI

abstract class BDcomAbstractModule extends AbstractModule
{
    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    private $physicalInterfaces = [];
    private $interfacesIds = [];

    function getPretty()
    {
        return [];
    }

    function __construct(Model $model) {
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if($fromCache && $ret = $this->getCache(json_encode($filter))) {
            return $ret;
        }
        $resp = $this->getPretty();
        $this->setCache(json_encode($filter), $resp, 15);
        return $resp;
    }

    protected function parseInterface($input)
    {
        $ifaces = $this->getInterfacesIds();
       if(is_numeric($input) && isset($ifaces[$input])) {
           return $ifaces[$input];
       }
       if (is_numeric($input)) {
           $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
               return $input == $iface['xid'];
           }));
           if(count($filtered) > 0) {
               return  $filtered[0];
           }
       }
       if (is_string($input)) {
           $input = strtolower(str_replace("t", "", $input));
           $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
               return $input == strtolower($iface['name']);
           }));
           if(count($filtered) > 0) {
               return  $filtered[0];
           }
       }

       if (is_string($input) && strpos($input, ".") !== false) {
           $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
               return $input == $iface['_llid_id'];
           }));
           if(count($filtered) > 0) {
               return  $filtered[0];
           }
       }
       throw new \InvalidArgumentException("Error find interface by ident='{$input}'");
    }

    function getInterfacesIds() {
        if(!$this->interfacesIds) {
            $this->loadInterfaces();
        }
        return $this->interfacesIds;
    }
    protected function getPhysicalInterfaces() {
        if(!$this->physicalInterfaces) {
            $this->loadInterfaces();
        }
        return $this->physicalInterfaces;
    }
    private function loadInterfaces() {
        $data = $this->getCache("interfaces_list", true);
        if($data) {
            $this->interfacesIds = $data['ifaces_list'];
            $this->physicalInterfaces = $data['ifaces_physical'];
            return $this;
        }
        $data = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Descr')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.llidSeqNumber')->getOid())
        ]));
        //Build physical interfaces list
        $this->physicalInterfaces = [];
        foreach ($this->getResponseByName('if.Descr', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            if (preg_match('/^GigaEthernet([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'GE',
                ];
            }
            if (preg_match('/^TGigaEthernet([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "tg{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'TGE',
                ];
            }
            if (preg_match('/^FastEthernet([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "fe{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'FE',
                ];
            }
            if (preg_match('/^EPON([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "epon{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'PON',
                ];
            }
            if (preg_match('/^g([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'GE',
                ];
            }
            if (preg_match('/^tg([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'TGE',
                ];
            }
            if (preg_match('/^epon([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $name = "epon{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $this->getIdByName($name),
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'PON',
                ];
            }
        }
        $ifaces = [];
        $llidSeqs = [];
        foreach($this->getResponseByName('ont.llidSeqNumber', $data)->fetchAll() as $item) {
            $xid = Helper::getIndexByOid($item->getOid(), 6);
            $parentIface = $this->findPhysicalInterface($xid, 'xid');
            if($parentIface) {
            $llidSeqs["{$parentIface['name']}:{$item->getValue()}"] = "." .
                Helper::getIndexByOid($item->getOid(), 6) . "." .
                Helper::getIndexByOid($item->getOid(), 5) . "." .
                Helper::getIndexByOid($item->getOid(), 4) . "." .
                Helper::getIndexByOid($item->getOid(), 3) . "." .
                Helper::getIndexByOid($item->getOid(), 2) . "." .
                Helper::getIndexByOid($item->getOid(), 1) . "." .
                Helper::getIndexByOid($item->getOid());
            }
        }
        foreach ($this->getResponseByName('if.Descr', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            $parentIface = $this->findPhysicalInterface($xid, 'xid');
            $type =  $parentIface ? $parentIface['type'] : 'ONU';
            //Fucking telnet - need to rename port
            $name = strtolower($iface->getValue());
            if (preg_match('/^GigaEthernet([0-9]\/[0-9]{1,3})/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
            }
            if (preg_match('/^g([0-9]\/[0-9]{1,3})/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
            }
            if (preg_match('/^tg([0-9]\/[0-9]{1,3})/', $iface->getValue(), $m)) {
                $name = "tg{$m[1]}";
            }
            if(strpos($iface->getValue(), "VLAN") !== false) {
                $type = 'vlan';
            };
            $id = $this->getIdByName($name);
            $ifaces[$id] = [
                'id' => $id,
                'xid' =>  $xid,
                'name' => $name,
                '_llid_id' => isset($llidSeqs[$name]) ? $llidSeqs[$name] : null,
                'type' => $type,
                'parent' => $this->findParentByName($name),
            ];
        }
        ksort($ifaces);
        sort($this->physicalInterfaces);
        $this->interfacesIds = $ifaces;
        $this->setCache("interfaces_list", [
            'ifaces_physical' => $this->physicalInterfaces,
            'ifaces_list' => $this->interfacesIds,
        ], 60, true);
        return $this;
    }

    private function findParentByName($name) {
        //EPON0/2:48
        if(preg_match('/epon0\/([0-9]{1,2}):([0-9]{1,3})/', $name, $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            return $id;
        }
        return  null;
    }
    private function getIdByName($name) {
        //EPON0/2:48
        if(preg_match('/epon0\/([0-9]{1,2}):([0-9]{1,3})/', $name, $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            $id += 100 * $matches[2];
            return $id;
        }
        if(preg_match('/epon0\/([0-9]{1,2})/', $name, $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            return $id;
        }
        if(preg_match('/g0\/([0-9]{1,2})/', $name, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }
    private function findPhysicalInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->physicalInterfaces, function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return $filtered[0];
        }
        return null;
    }
}
