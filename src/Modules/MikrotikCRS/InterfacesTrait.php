<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Monolog\Logger;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Exceptions\InterfaceNotFound;
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
        if($parseBy == 'id') {
            if(isset($ifaces[$iface])) {
                return $ifaces[$iface];
            }
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i['_port_num']) && $i['_port_num'] == $iface;
        });
        if(count($filtered) > 0) {
            return  array_values($filtered)[0];
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i[$parseBy]) && $i[$parseBy] == $iface;
        });
        if(count($filtered) > 0) {
            return  array_values($filtered)[0];
        }
        throw new InterfaceNotFound("Interface with name {$iface} not found");
    }

    private $_interfaces;

    function getInterfacesIds()
    {

        if ($this->_interfaces !== null) {
            return $this->_interfaces;
        }
        $info = $this->getCache('INTERFACES', true);
        if ($info !== null) {
            $this->_interfaces = $info;
            return $info;
        }
        $response = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
            Oid::init($this->oids->getOidByName('if.Name')->getOid()),
        ]);
        $responses = [];
        foreach ($response as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp->getResponse();
        }

        $ifaces = [];

        $types =  $this->oids->getOidByName('if.Type');
        foreach ($responses['if.Type'] as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            $type = $types->getValueNameById($r->getValue());
            $name = "$id";
            switch ($type) {
                case 'FE': $name = "eth/{$id}"; break;
                case 'LACP': $name = "lacp/{$id}"; break;
                case 'BRIDGE': $name = "br/{$id}"; break;
                case 'VLAN': $name = "vlan/{$id}"; break;
            }
            $ifaces[Helper::getIndexByOid($r->getOid())] = [
                'id' => (int)$id,
                'name' => $name,
                'type' => $type,
                '_snmp_id' => $id,
                '_dot1q_id' => null,
            ];
        }
        foreach ($responses['if.Name'] as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if(isset($ifaces[$id])) {
                $ifaces[$id]['name'] = $r->getValue();
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

}