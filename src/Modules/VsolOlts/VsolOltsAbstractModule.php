<?php

namespace SwitcherCore\Modules\VsolOlts;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

// GE0/6 - 10006
// GE0/7 - 10007
// EPON0/6 - 10006000
// EPON0/6:4 - 10006004

abstract class VsolOltsAbstractModule extends AbstractModule
{
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
        $ifaces = $this->getInterfaces();
        if(is_numeric($input) && isset($ifaces[$input])) {
            return $ifaces[$input];
        }
        if(is_numeric($input) && $input < 10000) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $iface['xid'] == $input;
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }

        if (is_string($input) && strpos($input, ".") !== false) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $iface['_snmp_id'] == $input;
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }
        if (is_string($input)) {
            $input = strtoupper(trim($input));
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == strtoupper($iface['name']);
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }
        throw new \InvalidArgumentException("Error find interface by ident='{$input}'");
    }

    private $_interfaces = [];
    protected function getInterfaces() {
        if($this->_interfaces) {
            return  $this->_interfaces;
        }
        $data = $this->getCache("interfaces_list", true);
        if($data) {
            $this->_interfaces = $data;
            return $data;
        }
        $data = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Name')->getOid())
        ]));
        $interfaces = [];
        foreach ($this->getResponseByName('if.Name', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            $id = $this->getIdByName($iface->getValue());
            if(!$id) {
                continue;
            }
            if(preg_match('/^EPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $iface->getValue(), $matches)) {
                $interfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    '_snmp_id' => "." .$matches[2] . "." . $matches[3],
                    'name' => $iface->getValue(),
                    'type' => 'ONU',
                    'technology' => 'epon',
                    'parent' => floor($id / 1000) * 1000,
                    '_slot' => (int)$matches[1],
                    '_port' => (int)$matches[2],
                    '_onu' => (int)$matches[3],
                ];
            } elseif (preg_match('/^EPON([0-9]{1,3})\/([0-9]{1,3})$/', $iface->getValue(), $matches)) {
                $interfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    '_snmp_id' => "." .$matches[2],
                    'name' => $iface->getValue(),
                    'type' => 'PON',
                    'technology' => 'epon',
                    'parent' => null,
                    '_slot' => (int)$matches[1],
                    '_port' => (int)$matches[2],
                    '_onu' => null,
                ];
            } elseif (preg_match('/^GE([0-9]{1,3})\/([0-9]{1,3})$/', $iface->getValue(), $matches)) {
                $interfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    '_snmp_id' => "." .$matches[2],
                    'name' => $iface->getValue(),
                    'type' => 'GE',
                    'technology' => null,
                    'parent' => null,
                    '_slot' => (int)$matches[1],
                    '_port' => (int)$matches[2],
                    '_onu' => null,
                ];
            }
        }
        $this->_interfaces = $interfaces;
        $this->setCache("interfaces_list", $interfaces, 30, true);
        return $interfaces;
    }

    private function getIdByName($name) {
        if(preg_match('/^EPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000000;
            $id += $matches[1] * 100000;
            $id += $matches[2] * 1000;
            $id += $matches[3];
            return $id;
        }
        if(preg_match('/^EPON([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000000;
            $id += $matches[1] * 100000;
            $id += $matches[2] * 1000;
            return $id;
        }
        if(preg_match('/^GE([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000;
            $id += $matches[1] * 100;
            $id += $matches[2];
            return $id;
        }
        return null;
    }

    function findIfaceByOid($oid, $offset = 0) {
        $xid = Helper::getIndexByOid($oid, 1 + $offset) . "." . Helper::getIndexByOid($oid, $offset);
        return $this->parseInterface($xid);
    }
}
