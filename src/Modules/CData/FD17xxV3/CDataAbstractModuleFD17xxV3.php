<?php

namespace SwitcherCore\Modules\CData\FD17xxV3;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class CDataAbstractModuleFD17xxV3 extends AbstractModule
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

    public function getPhysicalInterfaces() {
        $ifaces = $this->getInterfaces();
        return array_filter($ifaces, fn ($e) => $e['_onu'] === null);
    }

    public function getInterfaces()
    {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($ifaces = $this->getCache('INTERFACES', true)) {
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
            if (preg_match('/^(eth|ge|xge|gpon|epon)\s?([0-9])\/([0-9])\/([0-9]{1,2}):?([0-9]{1,3})?$/', $d->getValue(), $m)) {
                if ($m[1] === 'eth') {
                    $id = 100000 + ($m[3] * 10000) + ($m[4] * 100);
                    if(isset($m[5])) $id += $m[5];
                    $type = 'ETH';
                } elseif ($m[1] === 'ge') {
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
                    'name' => str_replace(" ", "", trim($d->getValue())),
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
        
        $data = $this->formatResponse($this->snmp->walk([
             \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.opticalRx')->getOid())
        ]));

        if ($data['ont.opticalRx']->error()) {
            throw new \SNMPException("Error get ont.opticalRx: " . $data['ont.opticalRx']->error());
        }

        $onu_list = [];
        foreach($data['ont.opticalRx']->fetchAll() as $d) {
            $snmp_id = Helper::getIndexByOid($d->getOid(), 2);
            $parent_snmp_id = Helper::getIndexByOid($d->getOid());
            if(!isset($indexes[$parent_snmp_id])) $indexes[$parent_snmp_id] = 0;
            $indexes[$parent_snmp_id]++;
            foreach($resp as $id => $arr) {
                if($arr['_snmp_id'] === $parent_snmp_id) {
                    $id = $id + $indexes[$parent_snmp_id];
                    $onu_list[$id]['id'] = (int)$id;
                    $onu_list[$id]['name'] = "{$arr['name']}:{$indexes[$parent_snmp_id]}";
                    $onu_list[$id]['parent'] = $arr['id'];
                    $onu_list[$id]['type'] = 'ONU';
                    $onu_list[$id]['_snmp_id'] = $snmp_id;
                    $onu_list[$id]['_type'] = 'gpon';
                    $onu_list[$id]['_shelf'] = $arr['_shelf'];
                    $onu_list[$id]['_slot'] = $arr['_slot'];
                    $onu_list[$id]['_port'] = $arr['_port'];
                    $onu_list[$id]['_onu'] = $indexes[$parent_snmp_id];
                    $onu_list[$id]['_technology'] = 'gpon';
                    break;
                }
            }
        }

        $resp = array_merge($resp, $onu_list);
        ksort($resp);

        $this->_interfaces = $resp;
        $this->setCache('INTERFACES', $resp, 600, true);
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

    protected function parseInterface($input, $searchBy=null)
    {
        //Определение, что это ID физ порта
        if(is_numeric($input) && $input >= 100000 && $input <= 300000) {
            $interface = $this->findInterface($input, 'id');
            if ($interface === null) {
                throw new \Exception("Interface with xid=$input not found");
            }
            return $interface;
        }

        //Определение, что это локальный ID пон-порта или ОНУ
        if (is_numeric($input) && $input >= 700000 && $searchBy != '_snmp_id') {
            //$portID = floor($input / 1000) * 1000;
            $interface = $this->findInterface($input, 'id');
            if ($interface === null) {
                throw new \Exception("Interface with id=$input not found");
            }
            return $interface;
        }

        //Поиск по _snmp_id или преобразование номера в ОНУ
        if (is_numeric($input) && $searchBy === '_snmp_id') {
            //Check is port
            if ($interface = $this->findInterface($input, '_snmp_id')) {
                return $interface;
            }
            //Find for ONU
            throw new \Exception("Interface with _smnp_id=$input not found");
        }

        if (!is_numeric($input)) {
            if (preg_match('/^(eth|ge|xe|xge|gpon|epon|pon)([0-9])\/([0-9])\/([0-9]{1,2})\:?([0-9]{1,3})?$/', str_replace(" ", "", trim($input)), $m)) {
                if($m[1] === 'xe') {
                    $m[1] = 'xge';
                }
                $interface = $this->findInterface("{$m[1]}{$m[2]}/{$m[3]}/{$m[4]}" . (isset($m[5]) ? ":{$m[5]}" : ""), 'name');
                if(!$interface) {
                    throw new \Exception("Can't find interface by name={$input}");
                } else {
                    return $interface;
                }
            }
        }

        throw new \InvalidArgumentException("Error parse interface by ident='{$input}'");
    }

    private function findInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->getInterfaces(), function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        return null;
    }

    // protected $_ontStatuses = null;

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    // function getOntIdsByInterface($interface, $onlyOnline = false)
    // {
    //     $interface = $this->parseInterface($interface);
    //     if ($interface['type'] !== 'PON') {
    //         return [(int)$interface['_snmp_id']];
    //     }
    //     $min = $interface['id'];
    //     $max = $min + 128;
    //     if (!$this->_ontStatuses) {
    //         $onts = $this->getModule('pon_onts_status')->run()->getPretty();
    //         $this->_ontStatuses = $onts;
    //     } else {
    //         $onts = $this->_ontStatuses;
    //     }

    //     $ontIds = [];
    //     foreach ($onts as $ont) {
    //         if ($ont['interface']['id'] > $min && $ont['interface']['id'] <= $max) {
    //             if ($onlyOnline && $ont['status'] !== 'Online') {
    //                 continue;
    //             }
    //             $ontIds[] = (int)$ont['interface']['_snmp_id'];
    //         }
    //     }
    //     return $ontIds;
    // }

    public function getOntIdsByInterface($interface, $onlyOnline = false) {
        $interface = $this->parseInterface($interface);
        if ($interface['type'] !== 'PON') return [(int)$interface['_snmp_id']];
        if (!$this->_ontStatuses) {
            $onts = $this->getModule('pon_onts_status')->run()->getPretty();
            $this->_ontStatuses = $onts;
        } else {
            $onts = $this->_ontStatuses;
        }
        $ontIds = [];
        foreach ($onts as $ont) {
            if ($ont['interface']['id'] === $interface['id']) {
                if ($onlyOnline && $ont['status'] !== 'Online') continue;
            }
            $ontIds[] = (int)$ont['interface']['_snmp_id'];
        }
        return $ontIds;
    }

    public function getAllOntsIfaces($onlyOnline = false) {
        $onts = $this->getModule('pon_onts_status')->run()->getPretty();
        $onts_ifaces = [];
        foreach ($onts as $ont) {
            if ($onlyOnline && $ont['status'] !== 'Online') {
                continue;
            }
            $iface = $ont['interface'];
            $parent_id = $iface['parent'];
            $parent_iface = $this->parseInterface($parent_id);
            $iface['_parent_snmp_id'] = $parent_iface['_snmp_id'];
            $onts_ifaces[] = $iface;
        }
        return $onts_ifaces;
    }



    // /**
    //  * @param $interface
    //  * @throws DependencyException
    //  * @throws NotFoundException
    //  */
    // function getAllOntsIds($onlyOnline = false)
    // {
    //     $onts = $this->getModule('pon_onts_status')->run()->getPretty();
    //     $ontIds = [];
    //     foreach ($onts as $ont) {
    //         if ($onlyOnline && $ont['status'] !== 'Online') {
    //             continue;
    //         }
    //         $ontIds[] = (int)$ont['interface']['_snmp_id'];
    //     }
    //     return $ontIds;
    // }

    protected function _exe($command)
    {
        $resp = $this->console->exec($command);
        if (strpos($resp, "Unknown command") !== false) {
            throw new \Exception($resp);
        }
        return true;
    }
}
