<?php

namespace SwitcherCore\Modules\Raisecom;

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


    function parseInterface($iface, $parseBy = 'id') {
        $check_port = false;
        $check_name = false;
        if(preg_match('/^([0-9]{1,4})\/([0-9]{1,4})$/', $iface)) $check_port = true;
        if(preg_match('/^(ge|tge|e)1\/\d{1,3}\/\d{1,3}$/', $iface)) $check_name = true;
        $ifaces = $this->getInterfacesIds();
        foreach($ifaces as $iface_id => $iface_arr) {
            if($iface_id == $iface) return $iface_arr;
            if($iface_arr['_port'] == $iface) return $iface_arr;
            if($check_port) {
                if($iface == "{$iface_arr['_unit']}/{$iface_arr['_port']}") {
                    return $iface_arr;
                }
            }
            if($check_name) {
                if($iface_arr['name'] == $iface) return $iface_arr;
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
        
        $this->snmp->setOidIncreasingCheck(false);
        $response = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Descr')->getOid())
        ])[0];
        if ($response->getError()) {
            throw new \Exception($response->getError());
        }
        $this->snmp->setOidIncreasingCheck(true);
        $ifaces = [];

        foreach ($response->getResponse() as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if (preg_match('/gigaethernet1\/([0-9]{1,3})\/([0-9]{1,3})$/', $r->getValue(), $m)) {
                $ifaces[$id] = [
                    'id' => (int)$id,
                    'name' => "ge1/{$m[1]}/{$m[2]}",
                    '_fullname' => "{$m[0]}",
                    '_snmp_id' => $id,
                    '_unit' => $m[1],
                    '_port' => $m[2],
                ];
            } elseif (preg_match('/tengigabitethernet1\/([0-9]{1,3})\/([0-9]{1,3})$/', $r->getValue(), $m)) {
                $ifaces[$id] = [
                    'id' => (int)$id,
                    'name' => "tge1/{$m[1]}/{$m[2]}",
                    '_fullname' => "{$m[0]}",
                    '_snmp_id' => $id,
                    '_unit' => $m[1],
                    '_port' => $m[2],
                ];
            } elseif (preg_match('/ethernet1\/([0-9]{1,3})\/([0-9]{1,3})$/', $r->getValue(), $m)) {
                $ifaces[$id] = [
                    'id' => (int)$id,
                    'name' => "e1/{$m[1]}/{$m[2]}",
                    '_fullname' => "{$m[0]}",
                    '_snmp_id' => $id,
                    '_unit' => $m[1],
                    '_port' => $m[2],
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $key;
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        $this->container->get(CacheInterface::class)->set($key, $value, $timeout);
        return true;
    }

    protected function getInterfacesWithConnectorTypeInfo() {
        $cached = $this->getCache('OPTICAL_MEDIA_INTERFACES', true);
        if($cached !== null) {
            return $cached;
        }

        $interfacesList = [];
        $data = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('sfp.mediaConnectorType')->getOid()),
        ]));
        if($data['sfp.mediaConnectorType']->error()) {
            throw new \Exception("Error get media types - " . $data['sfp.mediaConnectorType']->error());
        }
        $ifaceIds = [];
        foreach ($data['sfp.mediaConnectorType']->fetchAll() as $interface) {
            $ifaceIds[] = Helper::getIndexByOid($interface->getOid());
        }

        $ifcs = $this->getInterfacesIds();
        foreach($ifcs as $k => $ifc) {
            if(!in_array($ifc['_snmp_id'], $ifaceIds)) {
                continue;
            }
            $interfacesList[$ifc['_snmp_id']] = $ifc;
        }
        $this->setCache('OPTICAL_MEDIA_INTERFACES', $interfacesList, 600, true);
        return $interfacesList;
    }
}
