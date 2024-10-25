<?php

namespace SwitcherCore\Modules\VsolOlts\GPONV1600;

use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

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

    function __construct(Model $model)
    {
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if ($fromCache && $ret = $this->getCache(json_encode($filter))) {
            return $ret;
        }
        $resp = $this->getPretty();
        $this->setCache(json_encode($filter), $resp, 15);
        return $resp;
    }

    protected function parseInterface($input)
    {
        $ifaces = $this->getInterfaces();

        if (is_string($input) && strpos($input, ".") !== false) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return trim($iface['_snmp_id'], ".") === trim($input, ".");
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }
        if (is_string($input)) {
            $input = strtoupper(trim($input));
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == strtoupper($iface['name']);
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }
        if (is_numeric($input) && $input < 10000) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $iface['xid'] == $input;
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }
        if (is_numeric($input)) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $iface['id'] == $input;
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }

        throw new \InvalidArgumentException("Error find interface by ident='{$input}'");
    }

    private $_interfaces = [];
    private $physicalInterfaces = [];

    protected function getPhysicalInterfaces()
    {
        if (!$this->physicalInterfaces) {
            $this->getInterfaces();
        }
        return $this->physicalInterfaces;
    }

    protected function getOntInterfaces()
    {
        if (!$this->_interfaces) {
            $this->getInterfaces();
        }
        return $this->_interfaces;
    }

    protected function getInterfaces()
    {
        $data = $this->getCache("interfaces_list", true);
        if ($data) {
            $this->_interfaces = $data['ifaces_list'];
            $this->physicalInterfaces = $data['ifaces_physical'];
            return array_merge($this->_interfaces, $this->physicalInterfaces);
        }
        $data = $this->formatResponse($this->snmp->walkNext([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Name')->getOid())
        ]));

        $physicalInterfaces = [];
        $interfaces = [];
        foreach ($this->getResponseByName('if.Name', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            [$name] = explode(" ", Helper::hexToStr($iface->getHexValue()));

            $id = $this->getIdByName($name);
            if (!$id) {
                continue;
            }

            if (preg_match('/^GPON([0-9]{1,3})ONU([0-9]{1,3})$/', $name, $matches)) {
                $first_letter = $matches[0][0];
                $port = (int)$matches[1];
                $interfaces[$id] = [
                    'id' =>  (int)$id,
                    'xid' => $xid,
                    '_snmp_id' => "." . $port . "." . $matches[2],
                    'name' => $first_letter . "PON0/{$port}:{$matches[2]}",
                    'type' => 'ONU',
                    'parent' => 10000000 + $port * 1000,
                    '_slot' => 0,
                    '_port' => $port,
                    '_onu' => (int)$matches[2],
                ];
            } elseif (preg_match('/^GPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $name, $matches)) {
                $port = (int)$matches[2];
                $interfaces[$id] = [
                    'id' =>  (int)$id,
                    'xid' => $xid,
                    '_snmp_id' => "." . $matches[2] . "." . $matches[3],
                    'name' => $name,
                    'type' => 'ONU',
                    'parent' => 10000000 + $port * 1000,
                    '_slot' => (int)$matches[1],
                    '_port' => $port,
                    '_onu' => (int)$matches[3],
                ];
            } elseif (preg_match('/^GPON([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
                $physicalInterfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    '_snmp_id' => ".{$xid}",
                    'name' => $name,
                    'type' => 'PON',
                    'parent' => null,
                    '_slot' => (int)$matches[1],
                    '_port' => (int)$matches[2],
                    '_onu' => null,
                ];
            } elseif (preg_match('/^GE([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
                $physicalInterfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    '_snmp_id' => ".{$xid}",
                    'name' => $name,
                    'type' => 'GE',
                    'technology' => null,
                    'parent' => null,
                    '_slot' => (int)$matches[1],
                    '_port' =>  (int)$matches[2],
                    '_onu' => null,
                ];
            }
        }
        ksort($interfaces);
        sort($physicalInterfaces);
        $this->_interfaces = $interfaces;
        $this->physicalInterfaces = $physicalInterfaces;
        $this->setCache("interfaces_list", [
            'ifaces_list' => $interfaces,
            'ifaces_physical' => $physicalInterfaces,
        ], 300, true);
        return array_merge($interfaces, $physicalInterfaces);
    }

    private function getIdByName($name)
    {
        if (preg_match('/^EPON|GPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000000;
            $id += $matches[1] * 100000;
            $id += $matches[2] * 1000;
            $id += $matches[3];
            return $id;
        }
        if (preg_match('/^EPON|GPON([0-9]{1,3})ONU([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000000;
            $id += 0 * 100000;
            $id += $matches[1] * 1000;
            $id += $matches[2];
            return $id;
        }
        if (preg_match('/^EPON|GPON([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000000;
            $id += $matches[1] * 100000;
            $id += $matches[2] * 1000;
            return $id;
        }
        if (preg_match('/^GE([0-9]{1,3})\/([0-9]{1,3})$/', $name, $matches)) {
            $id = 10000;
            $id += $matches[1] * 100;
            $id += $matches[2];
            return $id;
        }
        return null;
    }

    function findIfaceByOid($oid, $offset = 0)
    {
        $xid = Helper::getIndexByOid($oid, 1 + $offset) . "." . Helper::getIndexByOid($oid, $offset);
        return $this->parseInterface($xid);
    }
}
