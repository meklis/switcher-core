<?php

namespace SwitcherCore\Modules\HuaweiOLT;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

// ethernet0/6/1 - 100601
// ethernet1/6/1 - 110601
// GPON 0/1/7 - 200107
// GPON 0/1/7:0 - 200107000
// GPON 0/1/7:1 - 200107001
//                200001010

abstract class HuaweiOLTAbstractModule extends AbstractModule
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
        $this->setCache(json_encode($filter), $resp, 5);
        return $resp;
    }

    protected function parseInterface($input)
    {
        $ifaces = $this->getInterfaces();
        if (is_numeric($input) && isset($ifaces[$input])) {
            return $ifaces[$input];
        }

        if (is_string($input) && strpos($input, ".") !== false) {
            list($xid, $onuNum) = explode(".", $input);
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($xid) {
                return $iface['xid'] == $xid;
            }));
            if (count($filtered) > 0) {
                $iface = $filtered[0];
                $iface['name'] .= ":{$onuNum}";
                $iface['parent'] = $iface['id'];
                $iface['type'] = 'ONU';
                $iface['_onu'] = $onuNum;
                $iface['id'] = ($iface['id'] * 1000) + $onuNum;
                $iface['xid'] .= ".{$onuNum}";
                return $iface;
            }
        }
        if (is_string($input) && strpos($input, ":") !== false) {
            list($portName, $onuNum) = explode(":", $input);
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($portName) {
                return $iface['name'] == $portName;
            }));
            if (count($filtered) > 0) {
                $iface = $filtered[0];
                $iface['name'] .= ":{$onuNum}";
                $iface['parent'] = $iface['id'];
                $iface['type'] = 'ONU';
                $iface['_onu'] = $onuNum;
                $iface['id'] = ($iface['id'] * 1000) + $onuNum;
                $iface['xid'] .= ".{$onuNum}";
                return $iface;
            }
        }

        if (is_numeric($input)) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == $iface['xid'];
            }));
            if (count($filtered) > 0) {
                return $filtered[0];
            }
        }
        if (is_numeric($input) && $input >= 200000000) {
            $ponPortId = floor($input / 1000);
            $onuNum = $input - ($ponPortId * 1000);
            if (isset($ifaces[$ponPortId])) {
                $iface = $ifaces[$ponPortId];
                $iface['name'] .= ":{$onuNum}";
                $iface['parent'] = $iface['id'];
                $iface['type'] = 'ONU';
                $iface['_onu'] = $onuNum;
                $iface['id'] = ($iface['id'] * 1000) + $onuNum;
                $iface['xid'] .= ".{$onuNum}";
                return $iface;
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
        throw new \InvalidArgumentException("Error find interface by ident='{$input}'");
    }

    protected function isHasEponIfaces()
    {
        $ifaces = array_filter($this->getInterfaces(), function ($f) {
            return $f['_technology'] === 'epon';
        });
        return count($ifaces) > 0;
    }

    protected function isHasGponIfaces()
    {
        $ifaces = array_filter($this->getInterfaces(), function ($f) {
            return $f['_technology'] === 'gpon';
        });
        return count($ifaces) > 0;
    }

    private $_interfaces = [];

    protected function getInterfaces()
    {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        $data = $this->getCache("interfaces_list", true);
        if ($data) {
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

            if (preg_match('/^(EPON|GPON) ([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2})$/', $iface->getValue(), $matches)) {
                $interfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $iface->getValue(),
                    'type' => 'PON',
                    '_technology' => strtolower($matches[1]),
                    '_pon_max_ont_size' => $matches[1] === 'EPON' ? 64 : 128,
                    'parent' => null,
                    '_shelf' => $matches[2],
                    '_frame' => $matches[2],
                    '_slot' => $matches[3],
                    '_port' => $matches[4],
                    '_onu' => null,
                ];
            } elseif (preg_match('/^ethernet([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2})$/', $iface->getValue(), $matches)) {
                $interfaces[$id] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $iface->getValue(),
                    'type' => 'ETH',
                    'parent' => null,
                    '_technology' => null,
                    '_shelf' => $matches[1],
                    '_frame' => $matches[1],
                    '_slot' => $matches[2],
                    '_port' => $matches[3],
                    '_onu' => null,
                ];
            }
        }
        $this->_interfaces = $interfaces;
        $this->setCache("interfaces_list", $interfaces, 180, true);
        return $interfaces;
    }

    private function findParentByName($name)
    {
        if (preg_match('/^[EG]PON ([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2})$/', $name, $matches)) {
            $id = 200000;
            $id += 10000 * $matches[1];
            $id += 100 * $matches[2];
            $id += $matches[3];
            return $id;
        }
        return null;
    }

    private function getIdByName($name)
    {
        if (preg_match('/^[EG]PON ([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2}):([0-9]{1,3})$/', $name, $matches)) {
            $id = 200000000;
            $id += 10000000 * $matches[1];
            $id += 100000 * $matches[2];
            $id += 1000 * $matches[3];
            $id += $matches[4];
            return $id;
        }
        if (preg_match('/^[EG]PON ([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2})$/', $name, $matches)) {
            $id = 200000;
            $id += 10000 * $matches[1];
            $id += 100 * $matches[2];
            $id += $matches[3];
            return $id;
        }

        if (preg_match('/^ethernet([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,2})$/', $name, $matches)) {
            $id = 100000;
            $id += 10000 * $matches[1];
            $id += 100 * $matches[2];
            $id += $matches[3];
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
