<?php

namespace SwitcherCore\Modules\HuaweiQuidwayS93;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Monolog\Logger;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CacheInterface;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\Objects\WrappedResponse;

trait InterfacesTrait
{
    /**
     * @var array | WrappedResponse[]
     */
    protected $response;

    /**
     * @var OidCollector
     */
    protected $oids;


    /**
     * @var MultiWalkerInterface
     */
    protected $snmp;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Container
     */
    protected $container;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Device
     */
    protected $device;


    function parseInterface($iface, $parseBy = 'id')
    {
        $ifaces = $this->getInterfacesIds();
        if ($parseBy == '_snmp_id' && is_numeric($iface) && isset($ifaces[$iface])) {
            return $ifaces[$iface];
        }
        if ($parseBy == 'id' && is_numeric($iface)) {
            $filteredList = array_filter($ifaces, function ($e) use ($iface) {
                return $iface == $e['id'];
            });
            if (count($filteredList) != 0) {
                return array_values($filteredList)[0];
            }
        } elseif (is_numeric($iface)) {
            $filteredList = array_filter($ifaces, function ($e) use ($iface) {
                return $iface == $e['_physical_id'];
            });
            if (count($filteredList) != 0) {
                return array_values($filteredList)[0];
            }
        } elseif (is_string($iface)) {
            $ifaces = array_filter($ifaces, function ($e) use ($iface) {
                return $iface == $e['name'];
            });
            if (count($ifaces) != 0) {
                return array_values($ifaces)[0];
            } else {
                throw new \Exception("Interface with name {$iface} not found");
            }
        }
        throw new \Exception("Interface with name {$iface} not found");
    }

    private $_interfaces;


    private $_physicalDevices = [];

    function getPhysicalDevices()
    {

        if ($this->_physicalDevices) {
            return $this->_physicalDevices;
        } elseif ($data = $this->getCache('PHYSICAL_DEVICES', true)) {
            return $data;
        }

        $responses = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('ent.physicalType')->getOid()),
            Oid::init($this->oids->getOidByName('ent.physicalName')->getOid())
        ]);
        foreach ($responses as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if ($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp->getResponse();
        }

        $physNames = [];
        foreach ($responses['ent.physicalName'] as $response) {
            $index = Helper::getIndexByOid($response->getOid());
            $physNames[$index] = $response->getValue();
        }

        foreach ($responses['ent.physicalType'] as $response) {
            $index = Helper::getIndexByOid($response->getOid());
            if (preg_match('/Port/', $response->getValue())) {
                $data['interfaces'][$index] = $physNames[$index];
                $data['interfaces_by_name'][$physNames[$index]] = $index;
            } elseif (preg_match('/slot/', $response->getValue())) {
                $data['slots'][$index] = $physNames[$index];
            } elseif (preg_match('/Board/', $response->getValue())) {
                $data['boards'][$index] = $physNames[$index];
            } elseif (preg_match('/PowerSupply/', $response->getValue())) {
                $data['power'][$index] = $physNames[$index];
            } elseif (preg_match('/Fan/', $response->getValue())) {
                $data['fans'][$index] = $physNames[$index];
            }
        }

        $this->_physicalDevices = $data;
        $this->setCache('PHYSICAL_DEVICES', $data, 600, true);

        return $data;
    }

    protected function getParentIfaceIDsByLACP($ifaceId)
    {
        $data = [];
        $resp = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.stackStatus')->getOid() . ".{$ifaceId}"),
        ]);

        $name = $this->oids->findOidById($resp[0]->getOid());
        if ($resp[0]->getError()) {
            $this->logger->error("Error get parent ifaces by LACP, oid {$name->getOid()}");
            return [];
        }
        foreach ($resp[0]->getResponse() as $r) {
            $data[] = Helper::getIndexByOid($r->getOid());
        }
        return  $data;
    }


    function getInterfacesIds()
    {

        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($info = $this->getCache('INTERFACES', true)) {
            $this->_interfaces = $info;
            return $info;
        }

        $physical = $this->getPhysicalDevices()['interfaces_by_name'];

        $responses = [];
        foreach ($this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Name')->getOid()),
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
        ]) as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if ($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp->getResponse();
        }

        $ifaceTypes = [];
        $typeOid = $this->oids->getOidByName('if.Type');
        foreach ($responses['if.Type'] as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            $ifaceTypes[$id]  = $typeOid->getValueNameById($r->getValue());
        }

        foreach ($responses['if.Name'] as $r) {
            $name = '';
            $id = Helper::getIndexByOid($r->getOid());
            $slot = null;
            $port = null;
            $shortName = '';
            if (preg_match('/(Eth|eth).*?(([0-9]{1,4})\/([0-9]{1,4})\/([0-9]{1,4}))$/', $r->getValue(), $m)) {
                $name = $r->getValue();
                $slot = $m[3];
                $port = $m[5];
            } elseif (preg_match('/(Eth-)/', $r->getValue(), $m)) {
                $name = $r->getValue();
            }
            if(preg_match('/XGigabitEthernet/', $r->getValue())) {
                $shortName="XGE$slot/0/$port";
            } elseif (preg_match('/GigabitEthernet/', $r->getValue())) {
                $shortName="GE$slot/0/$port";
            } elseif (preg_match('/^Eth-/', $r->getValue())) {
                $shortName=$r->getValue();
            }

            if(!$name) {
                continue;
            }

            $ifaces[$id] = [
                'id' => (int)$id,
                'name' => $name,
                'type' => isset($ifaceTypes[$id])  ? $ifaceTypes[$id] : null,
                '_physical_id' => isset($physical[$name]) ? $physical[$name] : null,
                '_slot' => $slot,
                '_port' => $port,
                '_snmp_id' => $id,
                '_lacp_ifaces' => null,
                '_short_name' => $shortName,
            ];
        }

        foreach ($ifaces as $id=>$iface) {
            if($iface['type'] === 'LACP') {
                $ifaces[$id]['_lacp_ifaces'] = array_map(function ($e) use ($ifaces) {
                    return $ifaces[$e];
                }, $this->getParentIfaceIDsByLACP($id));
            }
        }

        uasort($ifaces, function ($a, $b) {
            if ($a['_slot'] && $b['_slot'] && $a['_port'] && $b['_port']) {
                return (($a['_slot'] * 1000) + $a['_port']) > (($b['_slot'] * 1000) + $b['_port']);
            }
            return strcmp($a['name'], $b['name']);
        });

        $this->_interfaces = $ifaces;
        $this->setCache("INTERFACES", $ifaces, 600, true);
        return $ifaces;
    }

    /**
     *
     * Method for working with cache.
     * Cache method generate unique prefix key for isolating over device and modules
     *
     * @param $key
     * @return mixed|null
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected
    function getCache($key, $withoutClass = false)
    {
        if (!$this->container->has(CacheInterface::class)) {
            return null;
        }
        $cache = $this->container->get(CacheInterface::class);
        if ($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        return $cache->get($key);
    }

    /**
     *
     * Method for set value to cache
     * Cache method generate unique prefix key for isolating over device and modules
     *
     * @param $key
     * @param mixed $value Any value, not allow streams
     * @param int $timeout Timeouts in sec
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected
    function setCache($key, $value, $timeout = -1, $withoutClass = false)
    {
        if (!$this->device->getIp()) {
            throw new NotFoundException("Incorrect injected device, without device");
        }
        if (!$this->container->has(CacheInterface::class)) {
            $this->logger->notice("Cache interface not setted");
            return false;
        }
        if ($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        $this->container->get(CacheInterface::class)->set($key, $value, $timeout);
        return true;
    }

}