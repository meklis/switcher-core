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
    private $interfaces;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    function getPretty()
    {
        return [];
    }

    function __construct(Model $model) {
        $this->interfaces = $model->getExtraParamByName('interfaces');
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
           $input = strtolower($input);
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

    protected $_ifaces;
    function getInterfacesIds() {
        if($this->_ifaces) {
            return  $this->_ifaces;
        }
        $ifacesList = $this->getCache("interfaces_list", true);
        if($ifacesList) {
            $this->_ifaces = $ifacesList;
            return  $ifacesList;
        }
        $data = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Descr')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.llidSeqNumber')->getOid())
        ]));
        $ifaces = [];
        $llidSeqs = [];
        foreach($this->getResponseByName('ont.llidSeqNumber', $data)->fetchAll() as $item) {
            $xid = Helper::getIndexByOid($item->getOid(), 6);
            $parentIface = $this->findInterface($xid, 'xid');
            $llidSeqs["{$parentIface['name']}:{$item->getValue()}"] = "." .
                Helper::getIndexByOid($item->getOid(), 6) . "." .
                Helper::getIndexByOid($item->getOid(), 5) . "." .
                Helper::getIndexByOid($item->getOid(), 4) . "." .
                Helper::getIndexByOid($item->getOid(), 3) . "." .
                Helper::getIndexByOid($item->getOid(), 2) . "." .
                Helper::getIndexByOid($item->getOid(), 1) . "." .
                Helper::getIndexByOid($item->getOid());
        }
        foreach ($this->getResponseByName('if.Descr', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            $parentIface = $this->findInterface($xid, 'xid');
            $type =  $parentIface ? $parentIface['type'] : 'ONU';
            //Fucking telnet - need to rename port
            $name = strtolower($iface->getValue());
            if (preg_match('/^GigaEthernet([0-9]\/[0-9]{1,3})/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
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
        $this->_ifaces = $ifaces;
        $this->setCache("interfaces_list", $ifaces, 60, true);
        return $ifaces;
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
    private function findInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->interfaces, function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return $filtered[0];
        }
        return null;
    }
}
