<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class CDataAbstractModuleFD16xxV3 extends AbstractModule
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

    protected $_interfaces = [];

    function getPhysicalInterfaces()
    {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($ifaces = $this->getCache('PHYS_INTERFACES', true)) {
            $this->_interfaces = $ifaces;
            return $this->_interfaces;
        }
        $data = $this->formatResponse($this->snmp->walk([
             \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Descr')->getOid())
        ]));
        if ($data['if.Descr']->error()) {
            throw new \SNMPException("Error get if.Descr: " . $data['if.Descr']->error());
        }
        $resp = [];
        foreach ($data['if.Descr']->fetchAll() as $d) {
            $id = null;
            $type = null;
            if (preg_match('/^(ge|xge|gpon|epon) ([0-9])\/([0-9])\/([0-9]{1,2})$/', $d->getValue(), $m)) {
                if ($m[1] === 'ge') {
                    $id = 1000 + ($m[3] * 100) + $m[4];
                    $type = 'ETH';
                } elseif ($m[1] === 'xge') {
                    $id = 2000 + ($m[3] * 100) + $m[4];
                    $type = "ETH";
                } elseif ($m[1] === 'gpon') {
                    $id = 10000000 + ($m[3] * 100000) + ($m[4] * 1000);
                    $type = "PON";
                } elseif ($m[1] === 'epon') {
                    $id = 10000000 + ($m[3] * 100000) + ($m[4] * 1000);
                    $type = "PON";
                }
                $resp[$id] = [
                    'id' =>  (int)$id,
                    'name' => $d->getValue(),
                    'parent' => null,
                    'type' => $type,
                    '_snmp_id' => Helper::getIndexByOid($d->getOid()),
                    '_type' => $m[1],
                    '_shelf' => $m[2],
                    '_slot' => $m[3],
                    '_port' => $m[4],
                    '_onu' => null,
                    '_technology' => $type == 'PON' ? 'gpon' : null,
                ];
            }
        }
        if(!$resp) {
            $this->logger->debug(json_encode($data['if.Descr']->fetchAll(), JSON_PRETTY_PRINT));
            throw new \Exception("if.Descr returned empty response");
        }
        $this->_interfaces = $resp;
        $this->setCache('PHYS_INTERFACES', $resp, 600, true);
        return $resp;
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


    function encodeSnmpOid($value)
    {
        $fillData = function ($val, $size) {
            return str_pad(decbin($val), $size, '0', STR_PAD_LEFT);
        };
        if (preg_match('/^(ge|xge|gpon|epon) ([0-9])\/([0-9])\/([0-9]{1,2}):([0-9]{1,})$/', $value, $matches)) {
            $port = (int)$matches[4];
            $onuNum = (int)$matches[5] ;
            return bindec("010010" .
                $fillData($port - 1, 6) .
                $fillData($onuNum, 12));
        } else {
            throw new \Exception("Allow only for ONU");
        }

    }

    function decodeSnmpOid($oid)
    {
        $onuNum = 0;
        $binary = str_pad(decbin($oid), 24, '0', STR_PAD_LEFT);
        $type = bindec(substr($binary, 0, 6));

        $portOlt = bindec(substr($binary, 6, 6)) + 1;
        $onuNum = bindec(substr($binary, 12, 12));
        if(!$onuNum) {
            throw new \Exception("Unable to decode ONU number");
        }
        $parentIface =  array_filter($this->getPhysicalInterfaces(), function ($iface) use ($portOlt, $onuNum) {
            return $iface['_port'] == $portOlt && $iface['type'] == 'PON';
        });
        if(!$parentIface) {
            throw new \Exception("Unable to find parent PON port. Defined port - {$portOlt}");
        }
        $iface = array_shift($parentIface);

        return  [
            'id' => $iface['id'] + $onuNum,
            'name' => $iface['name'] . ":{$onuNum}",
            'parent' => $iface['id'],
            'type' => 'ONU',
            '_snmp_id' => $oid,
            '_onu' => $onuNum,
            '_type' => $iface['_type'],
            '_shelf' => $iface['_shelf'],
            '_slot' => $iface['_slot'],
            '_port' => $iface['_port'],
            '_technology' => 'gpon',
        ];
    }

    protected function parseInterface($input, $searchBy=null)
    {
        //Определение, что это ID физ порта
        if(is_numeric($input) && $input >= 1000 && $input <= 3000) {
            $interface = $this->findInterface($input, 'id');
            if ($interface === null) {
                throw new \Exception("Interface with xid=$input not found");
            }
            return $interface;
        }

        //Определение, что это локальный ID пон-порта или ОНУ
        if (is_numeric($input) && $input >= 10000000 && $searchBy != '_snmp_id') {
            $portID = floor($input / 1000) * 1000;
            $interface = $this->findInterface($portID, 'id');
            if ($interface === null) {
                throw new \Exception("Interface with id=$input not found");
            }
            $onu = ($input - 10000000) % 1000;
            if(!$onu) {
                return $interface;
            } else {
                $interface['type'] = 'ONU';
                $interface['parent'] = $portID;
                $interface['id'] = $input;
                $interface['name'] .=  ":{$onu}";
                $interface['_snmp_id'] = $this->encodeSnmpOid($interface['name']);
                $interface['_onu'] =  $onu;
                return $interface;
            }
        }

        //Поиск по _snmp_id или преобразование номера в ОНУ
        if (is_numeric($input) && $input > 10000  && ($input < 10000000 || $searchBy = '_snmp_id')) {
            //Check is port
            if ($interface = $this->findInterface($input, '_snmp_id')) {
                return $interface;
            }
            //Find for ONU
            return $this->decodeSnmpOid($input);
        }

        if (!is_numeric($input)) {
            if (preg_match('/^(ge|xe|xge|gpon|epon|pon)([0-9])\/([0-9])\/([0-9]{1,2})\:?([0-9]{1,3})?$/', str_replace(" ", "", trim($input)), $m)) {
                if($m[1] === 'xe') {
                    $m[1] = 'xge';
                }
                $interface = $this->findInterface("{$m[1]} {$m[2]}/{$m[3]}/{$m[4]}", 'name');
                if(!$interface) {
                    throw new \Exception("Can't find interface by name={$input}");
                }
                if(count($m) >= 6) {
                    $interface['type'] = 'ONU';
                    $interface['parent'] = $interface['id'];
                    $interface['id'] = $interface['id'] + (int)$m[5];
                    $interface['name'] .=  ":{$m[5]}";
                    $interface['_snmp_id'] = $this->encodeSnmpOid($interface['name']);
                    $interface['_onu'] =  (int)$m[5];
                }
                return $interface;
            }
        }

        throw new \InvalidArgumentException("Error parse interface by ident='{$input}'");
    }

    private function findInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->getPhysicalInterfaces(), function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        return null;
    }

    protected $_ontStatuses = null;

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getOntIdsByInterface($interface, $onlyOnline = false)
    {
        $interface = $this->parseInterface($interface);
        if ($interface['type'] !== 'PON') {
            return [(int)$interface['_snmp_id']];
        }
        $min = $interface['id'];
        $max = $min + 128;
        if (!$this->_ontStatuses) {
            $onts = $this->getModule('pon_onts_status')->run()->getPretty();
            $this->_ontStatuses = $onts;
        } else {
            $onts = $this->_ontStatuses;
        }

        $ontIds = [];
        foreach ($onts as $ont) {
            if ($ont['interface']['id'] > $min && $ont['interface']['id'] <= $max) {
                if ($onlyOnline && $ont['status'] !== 'Online') {
                    continue;
                }
                $ontIds[] = (int)$ont['interface']['_snmp_id'];
            }
        }
        return $ontIds;
    }

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getAllOntsIds($onlyOnline = false)
    {
        $onts = $this->getModule('pon_onts_status')->run()->getPretty();
        foreach ($onts as $ont) {
            if ($onlyOnline && $ont['status'] !== 'Online') {
                continue;
            }
            $ontIds[] = (int)$ont['interface']['_snmp_id'];
        }
        return $ontIds;
    }

    protected function _exe($command)
    {
        $resp = $this->console->exec($command);
        if (strpos($resp, "Unknown command") !== false) {
            throw new \Exception($resp);
        }
        return true;
    }
}
