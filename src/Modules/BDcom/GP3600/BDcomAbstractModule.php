<?php

namespace SwitcherCore\Modules\BDcom\GP3600;

use DI\DependencyException;
use DI\NotFoundException;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

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

    protected function parseInterface($input, $parseBy='id')
    {
        $ifaces = $this->getInterfacesIds();
        foreach ($this->getPhysicalInterfaces() as $physicalInterface) {
            $ifaces[$physicalInterface['id']] = $physicalInterface;
        }

        if (is_numeric($input) && $parseBy == 'xid') {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == $iface['xid'];
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }

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
       if (is_string($input)) {
           $input = strtolower($input);
           $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
               return $input == strtolower($iface['name']);
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
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Descr')->getOid())
        ]));
        //Build physical interfaces list
        $this->physicalInterfaces = [];
        $ifaces = [];
        foreach ($this->getResponseByName('if.Descr', $data)->fetchAll() as $iface) {
            $xid = (int) Helper::getIndexByOid($iface->getOid());
            $id = $this->getIdByName($iface->getValue());
            if (preg_match('/^GigaEthernet(([0-9])\/([0-9]{1,3}))$/', $iface->getValue(), $m)) {
                $name = "g{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'GE',
                    '_slot' => (int)$m[2],
                    '_port' => (int)$m[3],
                    '_type' => 'GigaEthernet',
                ];
            } else if (preg_match('/^TGigaEthernet(([0-9])\/([0-9]{1,3}))$/', $iface->getValue(), $m)) {
                $name = "tg{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'TGE',
                    '_slot' => (int)$m[2],
                    '_port' => (int)$m[3],
                    '_type' => 'TGigaEthernet',
                ];
            } else if (preg_match('/^gpon(([0-9])\/([0-9]{1,3}))$/', strtolower($iface->getValue()), $m)) {
                $name = "gpon{$m[1]}";
                $this->physicalInterfaces[] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $name,
                    'type' => 'PON',
                    '_slot' => (int)$m[2],
                    '_port' => (int)$m[3],
                    '_type' => 'gpon',
                ];
            } else if (preg_match('/^gpon0\/([0-9]{1,2}):([0-9]{1,3})$/', strtolower($iface->getValue()), $m)) {
                $ifaces[$id] = [
                    'id' => $id,
                    'xid' =>  $xid,
                    'name' => "gpon0/{$m[1]}:{$m[2]}",
                    'type' => 'ONU',
                    'parent' => $this->findParentByName($iface->getValue()),
                    '_slot' => 0,
                    '_port' => (int)$m[1],
                    '_onu_num' => (int)$m[2],
                    '_type' => 'gpon',
                ];
            }
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
        if(preg_match('/^gpon0\/([0-9]{1,2}):([0-9]{1,3})/', strtolower($name), $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            return $id;
        }
        return  null;
    }
    private function getIdByName($name) {
        //EPON0/2:48
        if(preg_match('/^gpon0\/([0-9]{1,2}):([0-9]{1,3})$/', strtolower($name), $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            $id += 100 * $matches[2];
            return $id;
        }
        if(preg_match('/^gpon0\/([0-9]{1,2})$/', strtolower($name), $matches)) {
            $id = 10000000;
            $id += 100000 * $matches[1];
            return $id;
        }
        if(preg_match('/^g0\/([0-9]{1,2})$/', $name, $matches)) {
            return 10 + (int)$matches[1];
        }
        if(preg_match('/^tg0\/([0-9]{1,2})$/', $name, $matches)) {
            return 100 + (int)$matches[1];
        }
        if(preg_match('/^GigaEthernet0\/([0-9]{1,2})$/', $name, $matches)) {
            return 10 + (int)$matches[1];
        }
        if(preg_match('/^TGigaEthernet0\/([0-9]{1,2})$/', $name, $matches)) {
            return 100 + (int)$matches[1];
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

    protected function sortResponseByInterface($response) {
        $RESP = [];
        foreach ($response as $resp) {
            if(isset($resp['interface']['id'])) {
                $RESP[$resp['interface']['id']] = $resp;
            } else if(isset($resp['interface'])) {
                $RESP[$resp['id']] = $resp;
            } else {
                throw new \Exception("Incorrect array for sorting by interface");
            }
        }
        ksort($RESP);
        return array_values($RESP);
    }

    /**
     * @param PoollerResponse[] $responses
     * @return void
     */
    protected function checkSnmpRespError($responses) {
        foreach ($responses as $response) {
            if($response->error) {
                throw new \SNMPException($response->error);
            }
        }
        return;
    }
}
