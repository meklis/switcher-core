<?php

namespace SwitcherCore\Modules\Huawei;

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
        if($parseBy == '_snmp_id' && is_numeric($iface) && isset($ifaces[$iface])) {
            return $ifaces[$iface];
        }
        if($parseBy == 'id' &&  is_numeric($iface)) {
            $filteredList = array_filter($ifaces, function ($e) use ($iface) {
                return $iface == $e['id'];
            });
            if(count($filteredList) != 0) {
                return array_values($filteredList)[0];
            }
        }
        if(preg_match('/^(Ge|eth)[0-9]{1,4}$/', $iface)) {
            $ifaces = array_filter($ifaces, function ($e) use ($iface) {
                return $iface == $e['name'];
            });
            if(count($ifaces) != 0) {
                return array_values($ifaces)[0];
            } else {
                throw new \Exception("Interface with name {$iface} not found");
            }
        }
        if (preg_match('/^(GigabitEthernet|Ethernet)(([0-9]{1,4})\/([0-9]{1,4})\/([0-9]{1,4}))$/', $iface, $m)) {
            $name = "eth{$m[5]}";
            if ($m[1] == 'GigabitEthernet') {
                $name = "Ge{$m[5]}";
            }
            $ifaces = array_filter($ifaces, function ($e) use ($name) {
                return $name == $e['name'];
            });
            if(count($ifaces) != 0) {
                return array_values($ifaces)[0];
            } else {
                throw new \Exception("Interface with name {$iface} not found");
            }
        }
        throw new \Exception("Interface with name {$iface} not found");
    }

    private $_interfaces;

    function getInterfacesIds()
    {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($info = $this->getCache('INTERFACES', true)) {
            $this->_interfaces = $info;
            return $info;
        }
        $response = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Name')->getOid())
        ])[0];
        if ($response->getError()) {
            throw new \Exception($response->getError());
        }
        $ifaces = [];

        $lastEthNum = 0;
        foreach ($response->getResponse() as $r) {
            if (preg_match('/^(GigabitEthernet|Ethernet)(([0-9]{1,4})\/([0-9]{1,4})\/([0-9]{1,4}))$/', $r->getValue(), $m)) {
                $name = "eth{$m[5]}";
                $id = $m[5];
                if($m[1] == "Ethernet") {
                    $lastEthNum  = $m[5];
                }
                if ($m[1] == 'GigabitEthernet') {
                    $name = "Ge{$m[5]}";
                    $id = $lastEthNum + $m[5];
                }

                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => $id ,
                    'name' => $name,
                    '_snmp_id' => Helper::getIndexByOid($r->getOid()),
                    '_type' => $m[1],
                    '_shelf' => $m[3],
                    '_slot' => $m[4],
                    '_port' => $m[5],
                ];
            }
        }
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
    protected function getCache($key, $withoutClass = false)
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
    protected function setCache($key, $value, $timeout = -1, $withoutClass = false)
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